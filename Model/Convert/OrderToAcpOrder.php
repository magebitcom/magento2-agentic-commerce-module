<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AcpSpec\Api\AgenticCheckout\OrderInterface as AcpOrderInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\OrderInterfaceFactory;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Api\Data\Webhook\EventDataInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Builds the order object a completed session returns, which the spec requires on completion.
 */
class OrderToAcpOrder
{
    /**
     * Discriminator the spec fixes for this object.
     */
    public const TYPE = 'order';

    /**
     * The route resolves this id through the shared order link, so the permalink is keyed by session.
     */
    public const PERMALINK_ROUTE = 'agentic_commerce/checkout/order';

    /**
     * @param OrderInterfaceFactory $orderFactory
     * @param UrlInterface $urlBuilder
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly OrderInterfaceFactory $orderFactory,
        private readonly UrlInterface $urlBuilder,
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * @param OrderInterface $order
     * @param string $sessionId
     * @return AcpOrderInterface
     */
    public function convert(OrderInterface $order, string $sessionId): AcpOrderInterface
    {
        $incrementId = (string) $order->getIncrementId();

        $acpOrder = $this->orderFactory->create();
        $acpOrder->setType(self::TYPE)
            ->setId($incrementId)
            ->setCheckoutSessionId($sessionId)
            ->setOrderNumber($incrementId)
            ->setPermalinkUrl($this->permalinkFor($sessionId))
            ->setStatus($this->statusOf($order));

        return $acpOrder;
    }

    /**
     * @param string $sessionId
     * @return string
     */
    public function permalinkFor(string $sessionId): string
    {
        return $this->urlBuilder->getUrl(self::PERMALINK_ROUTE, ['order_id' => $sessionId]);
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function statusOf(OrderInterface $order): string
    {
        foreach ($this->config->getOrderStatusMap((int) $order->getStoreId()) as $status) {
            if ($status['magento_order_status'] === $order->getStatus()) {
                return $status['ac_status'];
            }
        }

        return EventDataInterface::STATUS_CREATED;
    }
}
