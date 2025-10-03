<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     * @param SerializerInterface $serializer
     */
    public function __construct(
        public readonly ScopeConfigInterface $scopeConfig,
        public readonly UrlInterface $urlBuilder,
        public readonly SerializerInterface $serializer
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
        // @phpstan-ignore return.type
        return $this->scopeConfig->getValue(ConfigInterface::CONFIG_GTIN_SOURCE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSellerNameSource(?int $storeId = null): string
    {
        // @phpstan-ignore return.type
        return $this->scopeConfig->getValue(ConfigInterface::CONFIG_SELLER_NAME_SOURCE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string
     */
    public function getSellerName(?int $storeId = null): string
    {
        $source = $this->getSellerNameSource($storeId);

        if ($source === 'general') {
            // @phpstan-ignore return.type
            return $this->scopeConfig->getValue(
                'general/store_information/name',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        // @phpstan-ignore return.type
        return $this->scopeConfig->getValue(ConfigInterface::CONFIG_SELLER_NAME, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSellerPrivacyPolicyUrl(?int $storeId = null): string
    {
        /** @var string|null $url */
        $url = $this->scopeConfig->getValue(
            ConfigInterface::CONFIG_SELLER_PRIVACY_POLICY_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$url) {
            throw new LocalizedException(__('Seller privacy policy URL is not configured.'));
        }

        return $this->formatLink($url);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSellerTosUrl(?int $storeId = null): string
    {
        /** @var string|null $url */
        $url = $this->scopeConfig->getValue(
            ConfigInterface::CONFIG_SELLER_TOS_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$url) {
            throw new LocalizedException(__('Seller terms of service URL is not configured.'));
        }

        return $this->formatLink($url);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getReturnPolicyUrl(?int $storeId = null): string
    {
        /** @var string|null $url */
        $url = $this->scopeConfig->getValue(ConfigInterface::CONFIG_RETURN_POLICY_URL, ScopeInterface::SCOPE_STORE, $storeId);

        if (!$url) {
            throw new LocalizedException(__('Return policy URL is not configured.'));
        }

        return $this->formatLink($url);
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getReturnWindow(?int $storeId = null): int
    {
        // @phpstan-ignore cast.int
        return (int) $this->scopeConfig->getValue(ConfigInterface::CONFIG_RETURN_WINDOW, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCheckoutRouterBasePath(?int $storeId = null): string
    {
        /** @var string|null $value */
        $value = $this->scopeConfig->getValue(ConfigInterface::CONFIG_CHECKOUT_ROUTER_BASE_PATH, ScopeInterface::SCOPE_STORE, $storeId);

        if (!$value) {
            return 'checkout_sessions';
        }

        return trim($value, '/');
    }

    /**
     * @param int|null $storeId
     * @return array<array{type: string, link: string}>
     */
    public function getCheckoutSessionLinks(?int $storeId = null): array
    {
        /** @var string|null $links */
        $links = $this->scopeConfig->getValue(
            ConfigInterface::CONFIG_CHECKOUT_SESSION_LINKS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$links) {
            return [];
        }

        $links = $this->serializer->unserialize($links);

        if (!is_array($links)) {
            return [];
        }

        return array_map(function ($link) {
            return [
                'type' => $link['type'],
                'link' => $this->formatLink($link['link'])
            ];
        }, array_values($links));
    }

    /**
     * @param string $url
     * @return string
     */
    protected function formatLink(string $url): string
    {
        if (str_starts_with($url, '/')) {
            $url = rtrim($this->urlBuilder->getBaseUrl(), '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }
}
