<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api;

interface ConfigInterface
{
    public const CONFIG_FEED_ENABLED = 'agentic_commerce/general_settings/enable_feed';
    public const CONFIG_CHECKOUT_ENABLED = 'agentic_commerce/general_settings/enable_checkout';

    public const CONFIG_CHECKOUT_SESSION_LINKS = 'agentic_commerce/agentic_checkout/session_links';
    public const CONFIG_CHECKOUT_ROUTER_BASE_PATH = 'agentic_commerce/agentic_checkout/router_base_path';
    public const CONFIG_WEBHOOK_URL = 'agentic_commerce/agentic_checkout/webhook_url';
    public const CONFIG_WEBHOOK_SECRET = 'agentic_commerce/agentic_checkout/webhook_secret';
    public const CONFIG_WEBHOOKS_ENABLED = 'agentic_commerce/agentic_checkout/enable_webhooks';
    public const CONFIG_API_TOKEN = 'agentic_commerce/agentic_checkout/api_token';
    public const CONFIG_ORDER_STATUS_MAP = 'agentic_commerce/agentic_checkout/order_status_map';

    public const CONFIG_GTIN_SOURCE = 'agentic_commerce/product_feed/gtin_source';
    public const CONFIG_SELLER_NAME_SOURCE = 'agentic_commerce/product_feed/seller_name_source';
    public const CONFIG_SELLER_NAME = 'agentic_commerce/product_feed/seller_name';
    public const CONFIG_SELLER_PRIVACY_POLICY_URL = 'agentic_commerce/product_feed/seller_privacy_policy_url';
    public const CONFIG_MARKETING_CONSENT_SUBSCRIBES = 'agentic_commerce/general_settings/marketing_consent_subscribes';
    public const CONFIG_SELLER_TOS_URL = 'agentic_commerce/product_feed/seller_tos_url';
    public const CONFIG_RETURN_POLICY_URL = 'agentic_commerce/product_feed/return_policy_url';
    public const CONFIG_RETURN_WINDOW = 'agentic_commerce/product_feed/return_window';

    public const CONFIG_IDEMPOTENCY_TTL = 'agentic_commerce/idempotency/ttl';

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isFeedEnabled(?int $storeId = null): bool;

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCheckoutEnabled(?int $storeId = null): bool;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getGtinSourceAttribute(?int $storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSellerNameSource(?int $storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSellerName(?int $storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSellerPrivacyPolicyUrl(?int $storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSellerTosUrl(?int $storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getReturnPolicyUrl(?int $storeId = null): string;

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getReturnWindow(?int $storeId = null): int;

    /**
     * @param int|null $storeId
     * @return array<array{type:string,link:string}>
     */
    public function getCheckoutSessionLinks(?int $storeId = null): array;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCheckoutRouterBasePath(?int $storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getWebhookUrl(?int $storeId = null): string;

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getWebhookSecret(?int $storeId = null): string;

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getIdempotencyTtl(?int $storeId = null): int;

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function getIsWebhooksEnabled(?int $storeId = null): bool;

    /**
     * @param int|null $storeId
     * @return string|null
     */
    public function getApiToken(?int $storeId = null): ?string;

    /**
     * Get order status mapping configuration
     *
     * @param int|null $storeId
     * @return array<array{magento_order_status: string, ac_status: string}>
     */
    public function getOrderStatusMap(?int $storeId = null): array;

    /**
     * Whether a reported opt-in should create a newsletter subscriber. The decision is recorded on the
     * order regardless.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function marketingConsentSubscribes(?int $storeId = null): bool;
}
