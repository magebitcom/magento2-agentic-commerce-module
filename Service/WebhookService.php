<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Service;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Api\Data\Webhook\WebhookEventInterface;
use Magebit\AgenticCommerce\Model\Data\Webhook\WebhookEvent;
use Magebit\AgenticCore\Model\Webhook\Dispatcher;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * Queues webhook events. Delivery, signing and retries belong to the shared dispatcher, so order
 * placement no longer waits on the receiver.
 */
class WebhookService
{
    /**
     * @param Dispatcher $dispatcher
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected readonly Dispatcher $dispatcher,
        protected readonly ConfigInterface $config,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param WebhookEventInterface $webhookEvent
     * @param string $sessionId
     * @return void
     */
    public function dispatch(WebhookEventInterface $webhookEvent, string $sessionId): void
    {
        if (!$this->config->getIsWebhooksEnabled()) {
            return;
        }

        // toArray() lives on the DTO base rather than on the interface.
        /** @var WebhookEvent $webhookEvent */
        try {
            $this->dispatcher->enqueue(
                ComplianceService::IDEMPOTENCY_SCOPE,
                $this->config->getWebhookUrl(),
                (string) json_encode($webhookEvent->toArray()),
                $sessionId
            );
        } catch (CouldNotSaveException $exception) {
            // An order that is already placed must not be undone by a webhook that could not be queued.
            $this->logger->critical('Could not queue webhook', [
                'exception' => $exception,
                'session_id' => $sessionId,
            ]);
        }
    }
}
