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

use Magebit\AcpSpec\Api\AgenticCheckout\MarketingConsentInterface;
use Magebit\AgenticCommerce\Api\MarketingConsentHandlerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Routes each reported consent decision to the handler bound for its channel. Merchants add or replace
 * handlers in di.xml; a channel with no handler is left alone rather than failing the order.
 */
class HandlerPool
{
    /**
     * @param LoggerInterface $logger
     * @param array<string, MarketingConsentHandlerInterface> $handlers Keyed by channel
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly array $handlers = []
    ) {
    }

    /**
     * @param MarketingConsentInterface[] $consents
     * @param OrderInterface $order
     * @return void
     */
    public function apply(array $consents, OrderInterface $order): void
    {
        foreach ($consents as $consent) {
            $channel = (string) $consent->getChannel();
            $handler = $this->handlers[$channel] ?? null;

            if ($handler === null) {
                continue;
            }

            try {
                $handler->handle($channel, (bool) $consent->getOptedIn(), $order);
            } catch (\Throwable $exception) {
                // The order is already placed. A marketing signup that fails must not undo it.
                $this->logger->error('Could not apply a marketing consent decision', [
                    'exception' => $exception,
                    'channel' => $channel,
                    'order_id' => $order->getIncrementId(),
                ]);
            }
        }
    }

    /**
     * The channels a handler exists for, which is what the seller can honestly declare.
     *
     * @return string[]
     */
    public function getChannels(): array
    {
        return array_keys($this->handlers);
    }
}
