<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Feed\Trait;

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
