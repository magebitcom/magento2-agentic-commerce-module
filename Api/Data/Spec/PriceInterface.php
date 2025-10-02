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

interface PriceInterface
{
    public const PRICE = 'price';
    public const APPLICABLE_TAXES_FEES = 'applicable_taxes_fees';
    public const SALE_PRICE = 'sale_price';
    public const SALE_PRICE_EFFECTIVE_DATE = 'sale_price_effective_date';
    public const UNIT_PRICING_MEASURE = 'unit_pricing_measure';
    public const BASE_MEASURE = 'base_measure';
    public const PRICING_TREND = 'pricing_trend';

    /**
     * Get the price
     *
     * @return string
     */
    public function getPrice(): string;

    /**
     * Get the applicable taxes and fees
     *
     * @return string|null
     */
    public function getApplicableTaxesFees(): ?string;

    /**
     * Get the sale price
     *
     * @return string|null
     */
    public function getSalePrice(): ?string;

    /**
     * Get the sale price effective date
     *
     * @return string|null
     */
    public function getSalePriceEffectiveDate(): ?string;

    /**
     * Get the unit pricing measure
     *
     * @return string|null
     */
    public function getUnitPricingMeasure(): ?string;

    /**
     * Get the base measure
     *
     * @return string|null
     */
    public function getBaseMeasure(): ?string;

    /**
     * Get the pricing trend
     *
     * @return string|null
     */
    public function getPricingTrend(): ?string;
}
