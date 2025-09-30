<?php

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        public readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @return bool
     */
    public function isFeedEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            ConfigInterface::CONFIG_FEED_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isCheckoutEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            ConfigInterface::CONFIG_CHECKOUT_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
}
