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

interface GeoTaggingInterface
{
    public const GEO_PRICE = 'geo_price';
    public const GEO_AVAILABILITY = 'geo_availability';

    /**
     * Get the geo price
     *
     * @return string|null
     */
    public function getGeoPrice(): ?string;

    /**
     * Get the geo availability
     *
     * @return string|null
     */
    public function getGeoAvailability(): ?string;
}

