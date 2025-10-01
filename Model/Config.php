<?php

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        public readonly ScopeConfigInterface $scopeConfig,
        public readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isFeedEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            ConfigInterface::CONFIG_FEED_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCheckoutEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            ConfigInterface::CONFIG_CHECKOUT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getGtinSourceAttribute(?int $storeId = null): string
    {
        return $this->scopeConfig->getValue(ConfigInterface::CONFIG_GTIN_SOURCE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSellerNameSource(?int $storeId = null): string
    {
        return $this->scopeConfig->getValue(ConfigInterface::CONFIG_SELLER_NAME_SOURCE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string
     */
    public function getSellerName(?int $storeId = null): string
    {
        $source = $this->getSellerNameSource($storeId);

        if ($source === 'general') {
            return $this->scopeConfig->getValue(
                'general/store_information/name',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $this->scopeConfig->getValue(ConfigInterface::CONFIG_SELLER_NAME, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSellerPrivacyPolicyUrl(?int $storeId = null): string
    {
        $url = $this->scopeConfig->getValue(
            ConfigInterface::CONFIG_SELLER_PRIVACY_POLICY_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$url) {
            throw new LocalizedException(__('Seller privacy policy URL is not configured.'));
        }

        if (str_starts_with($url, '/')) {
            $url = rtrim($this->urlBuilder->getBaseUrl(), '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSellerTosUrl(?int $storeId = null): string
    {
        $url = $this->scopeConfig->getValue(
            ConfigInterface::CONFIG_SELLER_TOS_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$url) {
            throw new LocalizedException(__('Seller terms of service URL is not configured.'));
        }

        if (str_starts_with($url, '/')) {
            $url = rtrim($this->urlBuilder->getBaseUrl(), '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getReturnPolicyUrl(?int $storeId = null): string
    {
        $url = $this->scopeConfig->getValue(ConfigInterface::CONFIG_RETURN_POLICY_URL, ScopeInterface::SCOPE_STORE, $storeId);

        if (!$url) {
            throw new LocalizedException(__('Return policy URL is not configured.'));
        }

        if (str_starts_with($url, '/')) {
            $url = rtrim($this->urlBuilder->getBaseUrl(), '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getReturnWindow(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(ConfigInterface::CONFIG_RETURN_WINDOW, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
