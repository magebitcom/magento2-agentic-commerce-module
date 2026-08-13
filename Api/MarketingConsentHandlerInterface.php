<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * What a merchant does with a marketing consent decision. The spec transports the decision and says
 * nothing about acting on it, so this is the extension point: bind a handler per channel in di.xml.
 */
interface MarketingConsentHandlerInterface
{
    /**
     * Called once per channel the agent reported, whether the buyer opted in or out. An implementation
     * must handle both: an explicit opt-out is a decision, not an absence of one.
     *
     * @param string $channel
     * @param bool $optedIn
     * @param OrderInterface $order
     * @return void
     */
    public function handle(string $channel, bool $optedIn, OrderInterface $order): void;
}
