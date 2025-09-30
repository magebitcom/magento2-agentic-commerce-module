<?php

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\FulfillmentInterface;

trait WithFulfillmentData
{
    /**
     * Get the shipping
     *
     * @return string
     */
    public function getShipping(): string
    {
        return $this->getData(FulfillmentInterface::SHIPPING);
    }

    /**
     * Get the delivery estimate
     *
     * @return string|null
     */
    public function getDeliveryEstimate(): ?string
    {
        return $this->getData(FulfillmentInterface::DELIVERY_ESTIMATE);
    }
}
