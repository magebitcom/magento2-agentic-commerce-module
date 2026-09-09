<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\MarketingConsent;

use Magebit\AgenticCommerce\Api\MarketingConsentHandlerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Writes the decision onto the order as an audit trail. Always safe to run: it records what the agent
 * reported without acting on it, which is what a merchant needs to answer "who consented to what".
 */
class RecordOnOrder implements MarketingConsentHandlerInterface
{
    /**
     * Extension attribute-style key on the order, one entry per channel.
     */
    public const DATA_KEY = 'agentic_marketing_consent';

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(string $channel, bool $optedIn, OrderInterface $order): void
    {
        if (!$order instanceof Order) {
            return;
        }

        $stored = $order->getData(self::DATA_KEY);
        $recorded = is_string($stored) ? json_decode($stored, true) : null;
        $recorded = is_array($recorded) ? $recorded : [];
        $recorded[$channel] = $optedIn;

        $order->setData(self::DATA_KEY, (string) json_encode($recorded));
        $this->orderRepository->save($order);
    }
}
