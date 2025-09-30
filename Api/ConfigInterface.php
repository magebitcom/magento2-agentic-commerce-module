<?php

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api;

interface ConfigInterface
{
    public const CONFIG_FEED_ENABLED = 'agentic_commerce/general_settings/enable_feed';
    public const CONFIG_CHECKOUT_ENABLED = 'agentic_commerce/general_settings/enable_checkout';

    /**
     * @return bool
     */
    public function isFeedEnabled(): bool;

    /**
     * @return bool
     */
    public function isCheckoutEnabled(): bool;
}
