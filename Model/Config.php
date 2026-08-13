<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
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
     * @param int|null $storeId
     * @return int
     */
    public function getIdempotencyTtl(?int $storeId = null): int
    {
        $value = $this->scopeConfig->getValue(ConfigInterface::CONFIG_IDEMPOTENCY_TTL, ScopeInterface::SCOPE_STORE, $storeId);

        if (!$value) {
            return 24;
        }

        // @phpstan-ignore cast.int
        return (int) $value;
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getWebhookUrl(?int $storeId = null): string
    {
        /** @var string|null $url */
        $url = $this->scopeConfig->getValue(ConfigInterface::CONFIG_WEBHOOK_URL, ScopeInterface::SCOPE_STORE, $storeId);

        if (!$url) {
            throw new LocalizedException(__('Webhook URL is not configured.'));
        }

        return $url;
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getWebhookSecret(?int $storeId = null): string
    {
        /** @var string|null $secret */
        $secret = $this->scopeConfig->getValue(ConfigInterface::CONFIG_WEBHOOK_SECRET, ScopeInterface::SCOPE_STORE, $storeId);

        if (!$secret) {
            throw new LocalizedException(__('Webhook secret is not configured.'));
        }

        return $secret;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function getIsWebhooksEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(ConfigInterface::CONFIG_WEBHOOKS_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string|null
     */
    public function getApiToken(?int $storeId = null): ?string
    {
        /** @var string|null $token */
        $token = $this->scopeConfig->getValue(
            ConfigInterface::CONFIG_API_TOKEN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $token ?: null;
    }

    /**
     * Get order status mapping configuration
     *
     * @param int|null $storeId
     * @return array<array{magento_order_status: string, ac_status: string}>
     */
    public function getOrderStatusMap(?int $storeId = null): array
    {
        $value = $this->scopeConfig->getValue(
            ConfigInterface::CONFIG_ORDER_STATUS_MAP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $value = $this->serializer->unserialize($value);
        }

        if (!is_array($value)) {
            return [];
        }

        // Filter out invalid entries and ensure proper structure
        $mappings = [];
        foreach ($value as $mapping) {
            if (is_array($mapping)
                && isset($mapping['magento_order_status'])
                && isset($mapping['ac_status'])
            ) {
                $mappings[] = [
                    'magento_order_status' => (string) $mapping['magento_order_status'],
                    'ac_status' => (string) $mapping['ac_status']
                ];
            }
        }

        return $mappings;
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

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function marketingConsentSubscribes(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            ConfigInterface::CONFIG_MARKETING_CONSENT_SUBSCRIBES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
