<?php

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\GeoTaggingInterface;

trait WithGeoTaggingData
{
    /**
     * Get the geo price
     *
     * @return string|null
     */
    public function getGeoPrice(): ?string
    {
        return $this->getData(GeoTaggingInterface::GEO_PRICE);
    }

    /**
     * Get the geo availability
     *
     * @return string|null
     */
    public function getGeoAvailability(): ?string
    {
        return $this->getData(GeoTaggingInterface::GEO_AVAILABILITY);
    }
}
