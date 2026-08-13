<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AgenticCommerce\Api\Data\Webhook\WebhookEventInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\Webhook\EventDataInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\Webhook\EventDataInterface;
use Magebit\AgenticCommerce\Api\Data\Webhook\WebhookEventInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magebit\AgenticCommerce\Api\Data\Webhook\RefundInterface;

class OrderToOrderCreatedUpdatedWebhook
{
    /**
     * @param WebhookEventInterfaceFactory $webhookEventInterfaceFactory
     * @param EventDataInterfaceFactory $eventDataInterfaceFactory
     * @param OrderToAcpOrder $orderConverter
     */
    public function __construct(
        protected readonly WebhookEventInterfaceFactory $webhookEventInterfaceFactory,
        protected readonly EventDataInterfaceFactory $eventDataInterfaceFactory,
        protected readonly OrderToAcpOrder $orderConverter,
    ) {
    }

    /**
     * @param OrderInterface $order
     * @param string $type
     * @param string $sessionId
     * @param RefundInterface[] $refunds
     * @return WebhookEventInterface
     */
    public function execute(OrderInterface $order, string $type, string $sessionId, array $refunds = []): WebhookEventInterface
    {
        return $this->webhookEventInterfaceFactory->create([
            'data' => [
                'type' => $type,
                'event_data' => $this->eventDataInterfaceFactory->create(['data' => [
                    'type' => EventDataInterface::TYPE_ORDER,
                    'status' => $this->getOrderStatus($order),
                    'checkout_session_id' => $sessionId,
                    'permalink_url' => $this->orderConverter->permalinkFor($sessionId),
                    'refunds' => $refunds,
                ]]),
            ],
        ]);
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getOrderStatus(OrderInterface $order): string
    {
        return $this->orderConverter->statusOf($order);
    }
}
