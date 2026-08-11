<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Convert;

class ConvertPrice
{
    public const DEFAULT_EXPONENT = 2;

    /**
     * ISO 4217 currencies whose minor-unit exponent differs from the default of 2.
     *
     * @var array<string, int>
     */
    private const EXPONENTS = [
        'BIF' => 0,
        'CLP' => 0,
        'DJF' => 0,
        'GNF' => 0,
        'ISK' => 0,
        'JPY' => 0,
        'KMF' => 0,
        'KRW' => 0,
        'PYG' => 0,
        'RWF' => 0,
        'UGX' => 0,
        'UYI' => 0,
        'VND' => 0,
        'VUV' => 0,
        'XAF' => 0,
        'XOF' => 0,
        'XPF' => 0,
        'BHD' => 3,
        'IQD' => 3,
        'JOD' => 3,
        'KWD' => 3,
        'LYD' => 3,
        'OMR' => 3,
        'TND' => 3,
    ];

    /**
     * Convert a major-unit amount to the currency's ISO 4217 minor units.
     *
     * @param float $amount
     * @param string $currencyCode
     * @return int
     */
    public function execute(float $amount, string $currencyCode): int
    {
        // Round on the decimal string: 19.99 * 100 is 1998.9999999999998, which (int) truncates to 1998.
        $decimal = number_format($amount, $this->getExponent($currencyCode), '.', '');

        return (int) str_replace('.', '', $decimal);
    }

    /**
     * Number of decimal places the currency's minor unit is expressed in.
     *
     * @param string $currencyCode
     * @return int
     */
    public function getExponent(string $currencyCode): int
    {
        return self::EXPONENTS[strtoupper($currencyCode)] ?? self::DEFAULT_EXPONENT;
    }
}
