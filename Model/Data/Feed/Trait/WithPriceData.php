<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Feed\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\PriceInterface;

trait WithPriceData
{
    /**
     * Get the price
     *
     * @return string
     */
    public function getPrice(): string
    {
        return $this->getData(PriceInterface::PRICE);
    }

    /**
     * Get the applicable taxes and fees
     *
     * @return string|null
     */
    public function getApplicableTaxesFees(): ?string
    {
        return $this->getData(PriceInterface::APPLICABLE_TAXES_FEES);
    }

    /**
     * Get the sale price
     *
     * @return string|null
     */
    public function getSalePrice(): ?string
    {
        return $this->getData(PriceInterface::SALE_PRICE);
    }

    /**
     * Get the sale price effective date
     *
     * @return string|null
     */
    public function getSalePriceEffectiveDate(): ?string
    {
        return $this->getData(PriceInterface::SALE_PRICE_EFFECTIVE_DATE);
    }

    /**
     * Get the unit pricing measure
     *
     * @return string|null
     */
    public function getUnitPricingMeasure(): ?string
    {
        return $this->getData(PriceInterface::UNIT_PRICING_MEASURE);
    }

    /**
     * Get the base measure
     *
     * @return string|null
     */
    public function getBaseMeasure(): ?string
    {
        return $this->getData(PriceInterface::BASE_MEASURE);
    }

    /**
     * Get the pricing trend
     *
     * @return string|null
     */
    public function getPricingTrend(): ?string
    {
        return $this->getData(PriceInterface::PRICING_TREND);
    }
}
