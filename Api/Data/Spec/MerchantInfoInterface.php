<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data\Spec;

interface MerchantInfoInterface
{
    public const SELLER_NAME = 'seller_name';
    public const SELLER_URL = 'seller_url';
    public const SELLER_PRIVACY_POLICY = 'seller_privacy_policy';
    public const SELLER_TOS = 'seller_tos';

    /**
     * Get the seller name
     *
     * @return string
     */
    public function getSellerName(): string;

    /**
     * Get the seller URL
     *
     * @return string
     */
    public function getSellerUrl(): string;

    /**
     * Get the seller privacy policy
     *
     * @return string|null
     */
    public function getSellerPrivacyPolicy(): ?string;

    /**
     * Get the seller terms of service
     *
     * @return string|null
     */
    public function getSellerTos(): ?string;
}

