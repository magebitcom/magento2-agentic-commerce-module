<?php

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\MerchantInfoInterface;

trait WithMerchantInfoData
{
    /**
     * Get the seller name
     *
     * @return string
     */
    public function getSellerName(): string
    {
        return $this->getData(MerchantInfoInterface::SELLER_NAME);
    }

    /**
     * Get the seller URL
     *
     * @return string
     */
    public function getSellerUrl(): string
    {
        return $this->getData(MerchantInfoInterface::SELLER_URL);
    }

    /**
     * Get the seller privacy policy
     *
     * @return string|null
     */
    public function getSellerPrivacyPolicy(): ?string
    {
        return $this->getData(MerchantInfoInterface::SELLER_PRIVACY_POLICY);
    }

    /**
     * Get the seller terms of service
     *
     * @return string|null
     */
    public function getSellerTos(): ?string
    {
        return $this->getData(MerchantInfoInterface::SELLER_TOS);
    }
}
