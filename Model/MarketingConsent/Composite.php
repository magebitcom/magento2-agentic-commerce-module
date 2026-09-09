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

/**
 * Runs several handlers for one channel, so a merchant can add their own alongside the shipped ones
 * instead of replacing them.
 */
class Composite implements MarketingConsentHandlerInterface
{
    /**
     * @param MarketingConsentHandlerInterface[] $handlers
     */
    public function __construct(
        private readonly array $handlers = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(string $channel, bool $optedIn, OrderInterface $order): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handle($channel, $optedIn, $order);
        }
    }
}
