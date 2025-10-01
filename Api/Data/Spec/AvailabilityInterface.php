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

interface AvailabilityInterface
{
    public const AVAILABILITY = 'availability';
    public const AVAILABILITY_DATE = 'availability_date';
    public const INVENTORY_QUANTITY = 'inventory_quantity';
    public const EXPIRATION_DATE = 'expiration_date';
    public const PICKUP_METHOD = 'pickup_method';
    public const PICKUP_SLA = 'pickup_sla';

    /**
     * Get the availability
     *
     * @return string
     */
    public function getAvailability(): string;

    /**
     * Get the availability date
     *
     * @return string|null
     */
    public function getAvailabilityDate(): ?string;

    /**
     * Get the inventory quantity
     *
     * @return int
     */
    public function getInventoryQuantity(): int;

    /**
     * Get the expiration date
     *
     * @return string|null
     */
    public function getExpirationDate(): ?string;

    /**
     * Get the pickup method
     *
     * @return string|null
     */
    public function getPickupMethod(): ?string;

    /**
     * Get the pickup SLA
     *
     * @return string|null
     */
    public function getPickupSla(): ?string;
}

