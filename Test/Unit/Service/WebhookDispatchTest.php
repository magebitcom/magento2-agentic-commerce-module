<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Service;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Model\Data\Webhook\WebhookEvent;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magebit\AgenticCommerce\Service\WebhookService;
use Magebit\AgenticCore\Model\Webhook\Dispatcher;
use Magento\Framework\Exception\CouldNotSaveException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * The digest itself is the shared signer's business now; what is left here is that dispatching queues
 * instead of sending, so order placement no longer waits on the receiver.
 */
class WebhookDispatchTest extends TestCase
{
    private const URL = 'https://receiver.test/hook';
    private const SESSION_ID = 'sess_123';

    /**
     * @return void
     */
    public function testDispatchEnqueuesRatherThanSending(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())
            ->method('enqueue')
            ->with(
                ComplianceService::IDEMPOTENCY_SCOPE,
                self::URL,
                $this->stringContains('order_updated'),
                self::SESSION_ID
            );

        $this->service($dispatcher, enabled: true)->dispatch($this->event(), self::SESSION_ID);
    }

    /**
     * @return void
     */
    public function testNothingIsQueuedWhenWebhooksAreDisabled(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->never())->method('enqueue');

        $this->service($dispatcher, enabled: false)->dispatch($this->event(), self::SESSION_ID);
    }

    /**
     * An order that is already placed must not be undone by a webhook that could not be queued.
     *
     * @return void
     */
    public function testAFailedEnqueueIsLoggedRatherThanThrown(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->method('enqueue')->willThrowException(new CouldNotSaveException(__('nope')));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('critical');

        $this->service($dispatcher, enabled: true, logger: $logger)
            ->dispatch($this->event(), self::SESSION_ID);
    }

    /**
     * @param Dispatcher&MockObject $dispatcher
     * @param bool $enabled
     * @param LoggerInterface|null $logger
     * @return WebhookService
     */
    private function service(
        Dispatcher $dispatcher,
        bool $enabled,
        ?LoggerInterface $logger = null
    ): WebhookService {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getIsWebhooksEnabled')->willReturn($enabled);
        $config->method('getWebhookUrl')->willReturn(self::URL);

        return new WebhookService($dispatcher, $config, $logger ?? $this->createMock(LoggerInterface::class));
    }

    /**
     * @return WebhookEvent
     */
    private function event(): WebhookEvent
    {
        $event = $this->getMockBuilder(WebhookEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArray'])
            ->getMock();
        $event->method('toArray')->willReturn(['type' => 'order_updated']);

        /** @var WebhookEvent $event */
        return $event;
    }
}
