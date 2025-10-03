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
