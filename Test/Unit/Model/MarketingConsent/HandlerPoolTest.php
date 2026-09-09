<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\MarketingConsent;

use Magebit\AcpSpec\Api\AgenticCheckout\MarketingConsentInterface;
use Magebit\AcpSpec\Data\AgenticCheckout\MarketingConsent;
use Magebit\AgenticCommerce\Api\MarketingConsentHandlerInterface;
use Magebit\AgenticCommerce\Model\MarketingConsent\HandlerPool;
use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class HandlerPoolTest extends TestCase
{
    /**
     * @return void
     */
    public function testEachChannelGoesToItsOwnHandler(): void
    {
        $email = $this->handler();
        $sms = $this->handler();

        $email->expects($this->once())->method('handle')->with('email', true);
        $sms->expects($this->once())->method('handle')->with('sms', false);

        $this->pool(['email' => $email, 'sms' => $sms])->apply(
            [$this->consent('email', true), $this->consent('sms', false)],
            $this->order()
        );
    }

    /**
     * An opt-out is a decision the merchant may need to act on, so it reaches the handler too.
     *
     * @return void
     */
    public function testAnOptOutStillReachesTheHandler(): void
    {
        $handler = $this->handler();
        $handler->expects($this->once())->method('handle')->with('email', false);

        $this->pool(['email' => $handler])->apply([$this->consent('email', false)], $this->order());
    }

    /**
     * A channel the merchant declared no handler for is skipped rather than failing the order.
     *
     * @return void
     */
    public function testAnUnhandledChannelIsSkipped(): void
    {
        $this->expectNotToPerformAssertions();

        $this->pool([])->apply([$this->consent('whatsapp', true)], $this->order());
    }

    /**
     * An order that is already placed must not be undone because a newsletter signup failed.
     *
     * @return void
     */
    public function testAFailingHandlerDoesNotPropagate(): void
    {
        $handler = $this->handler();
        $handler->method('handle')->willThrowException(new \RuntimeException('subscription service down'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');

        $this->pool(['email' => $handler], $logger)->apply([$this->consent('email', true)], $this->order());
    }

    /**
     * @return void
     */
    public function testNoConsentsMeansNoCalls(): void
    {
        $handler = $this->handler();
        $handler->expects($this->never())->method('handle');

        $this->pool(['email' => $handler])->apply([], $this->order());
    }

    /**
     * @param array<string, MarketingConsentHandlerInterface> $handlers
     * @param LoggerInterface|null $logger
     * @return HandlerPool
     */
    private function pool(array $handlers, ?LoggerInterface $logger = null): HandlerPool
    {
        return new HandlerPool($logger ?? $this->createMock(LoggerInterface::class), $handlers);
    }

    /**
     * @return MarketingConsentHandlerInterface&MockObject
     */
    private function handler(): MarketingConsentHandlerInterface
    {
        return $this->createMock(MarketingConsentHandlerInterface::class);
    }

    /**
     * @param string $channel
     * @param bool $optedIn
     * @return MarketingConsentInterface
     */
    private function consent(string $channel, bool $optedIn): MarketingConsentInterface
    {
        return new MarketingConsent(['channel' => $channel, 'opted_in' => $optedIn]);
    }

    /**
     * @return OrderInterface
     */
    private function order(): OrderInterface
    {
        return $this->createMock(OrderInterface::class);
    }
}
