<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

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

