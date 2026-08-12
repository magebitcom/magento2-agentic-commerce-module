<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magebit\AgenticCommerce\Service\WebhookService;
use Magebit\AgenticCommerce\Api\Data\Webhook\WebhookEventInterface;
use Magebit\AgenticCore\Api\OrderLinkRepositoryInterface;
use Magebit\AgenticCore\Model\Money\MinorUnits;
use Magento\Sales\Model\Order\Creditmemo;
use Magebit\AgenticCommerce\Model\Convert\OrderToOrderCreatedUpdatedWebhook;
use Magebit\AgenticCommerce\Api\Data\Webhook\RefundInterface;
use Magebit\AgenticCommerce\Api\Data\Webhook\RefundInterfaceFactory;

class SalesOrderCreditmemoRefundObserver implements ObserverInterface
{
    /**
     * @param LoggerInterface $logger
     * @param WebhookService $webhookService
     * @param RefundInterfaceFactory $refundInterfaceFactory
     * @param OrderToOrderCreatedUpdatedWebhook $orderToOrderCreatedUpdatedWebhook
     * @param MinorUnits $minorUnits
     * @param OrderLinkRepositoryInterface $orderLinkRepository
     */
    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly WebhookService $webhookService,
        protected readonly RefundInterfaceFactory $refundInterfaceFactory,
        protected readonly OrderToOrderCreatedUpdatedWebhook $orderToOrderCreatedUpdatedWebhook,
        protected readonly MinorUnits $minorUnits,
        protected readonly OrderLinkRepositoryInterface $orderLinkRepository,
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();

        // A reverse lookup: which session produced this order. Nothing agentic about the order
        // means nothing to notify.
        $orderEntityId = $creditmemo->getOrder()->getEntityId();
        $sessionId = is_numeric($orderEntityId)
            ? $this->orderLinkRepository->findSessionId(
                ComplianceService::IDEMPOTENCY_SCOPE,
                (int) $orderEntityId
            )
            : null;

        if ($sessionId === null) {
            return;
        }

        // Grand total is denominated in the order currency, not the base currency.
        $currencyCode = (string) ($creditmemo->getOrderCurrencyCode() ?: 'USD');

        /** @var RefundInterface $refund */
        $refund = $this->refundInterfaceFactory->create(['data' => [
            'type' => 'original_payment',
            'amount' => $this->minorUnits->convert((float) $creditmemo->getGrandTotal(), $currencyCode),
        ]]);

        $webhookEvent = $this->orderToOrderCreatedUpdatedWebhook->execute(
            $creditmemo->getOrder(),
            WebhookEventInterface::TYPE_ORDER_UPDATED,
            $sessionId,
            [
                $refund,
            ]
        );

        $this->webhookService->dispatch($webhookEvent, $sessionId);
    }
}
