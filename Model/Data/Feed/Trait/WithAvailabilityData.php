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

use Magebit\AgenticCommerce\Api\Data\Spec\AvailabilityInterface;

trait WithAvailabilityData
{
    /**
     * Get the availability
     *
     * @return string
     */
    public function getAvailability(): string
    {
        return $this->getData(AvailabilityInterface::AVAILABILITY);
    }

    /**
     * Get the availability date
     *
     * @return string|null
     */
    public function getAvailabilityDate(): ?string
    {
        return $this->getData(AvailabilityInterface::AVAILABILITY_DATE);
    }

    /**
     * Get the inventory quantity
     *
     * @return int
     */
    public function getInventoryQuantity(): int
    {
        return $this->getData(AvailabilityInterface::INVENTORY_QUANTITY);
    }

    /**
     * Get the expiration date
     *
     * @return string|null
     */
    public function getExpirationDate(): ?string
    {
        return $this->getData(AvailabilityInterface::EXPIRATION_DATE);
    }

    /**
     * Get the pickup method
     *
     * @return string|null
     */
    public function getPickupMethod(): ?string
    {
        return $this->getData(AvailabilityInterface::PICKUP_METHOD);
    }

    /**
     * Get the pickup SLA
     *
     * @return string|null
     */
    public function getPickupSla(): ?string
    {
        return $this->getData(AvailabilityInterface::PICKUP_SLA);
    }
}
