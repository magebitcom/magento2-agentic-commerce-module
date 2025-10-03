<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data;

use Magebit\AgenticCommerce\Api\Data\FulfillmentOptionShippingInterface;

/**
 * Fulfillment Option Shipping Data Transfer Object
 */
class FulfillmentOptionShipping extends FulfillmentOption implements FulfillmentOptionShippingInterface
{
    /**
     * @inheritDoc
     */
    public function getCarrier(): ?string
    {
        return $this->getData('carrier');
    }

    /**
     * @inheritDoc
     */
    public function setCarrier(?string $carrier): FulfillmentOptionShippingInterface
    {
        return $this->setData('carrier', $carrier);
    }

    /**
     * @inheritDoc
     */
    public function getEarliestDeliveryTime(): ?string
    {
        return $this->getData('earliest_delivery_time');
    }

    /**
     * @inheritDoc
     */
    public function setEarliestDeliveryTime(?string $time): FulfillmentOptionShippingInterface
    {
        return $this->setData('earliest_delivery_time', $time);
    }

    /**
     * @inheritDoc
     */
    public function getLatestDeliveryTime(): ?string
    {
        return $this->getData('latest_delivery_time');
    }

    /**
     * @inheritDoc
     */
    public function setLatestDeliveryTime(?string $time): FulfillmentOptionShippingInterface
    {
        return $this->setData('latest_delivery_time', $time);
    }
}
