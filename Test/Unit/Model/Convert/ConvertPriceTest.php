<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\Convert;

use Magebit\AgenticCommerce\Model\Convert\ConvertPrice;
use PHPUnit\Framework\TestCase;

class ConvertPriceTest extends TestCase
{
    /**
     * @var ConvertPrice
     */
    private ConvertPrice $convertPrice;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->convertPrice = new ConvertPrice();
    }

    /**
     * @dataProvider executeDataProvider
     * @param float $amount
     * @param string $currencyCode
     * @param int $expected
     * @return void
     */
    public function testExecute(float $amount, string $currencyCode, int $expected): void
    {
        $this->assertSame($expected, $this->convertPrice->execute($amount, $currencyCode));
    }

    /**
     * @return array<string, array{0: float, 1: string, 2: int}>
     */
    public static function executeDataProvider(): array
    {
        return [
            // Values the previous float-multiplication implementation truncated.
            'usd 19.99 truncated to 1998' => [19.99, 'USD', 1999],
            'usd 0.29 truncated to 28' => [0.29, 'USD', 29],
            'usd 1.15 truncated to 114' => [1.15, 'USD', 115],
            'usd 2.675 truncated to 267' => [2.675, 'USD', 268],
            'usd 1000.005 truncated to 100000' => [1000.005, 'USD', 100001],

            'usd zero' => [0.0, 'USD', 0],
            'usd whole amount' => [10.0, 'USD', 1000],
            'usd half rounds up' => [0.005, 'USD', 1],
            'usd extra precision rounds up' => [123.456, 'USD', 12346],

            // Discounts arrive as negative amounts; rounding is half away from zero.
            'usd negative' => [-19.99, 'USD', -1999],
            'usd negative sub-unit' => [-0.29, 'USD', -29],
            'usd negative half rounds away from zero' => [-2.675, 'USD', -268],

            // ISO 4217 exponent 0 — the minor unit is the major unit.
            'jpy whole amount' => [1000.0, 'JPY', 1000],
            'jpy rounds down' => [1000.4, 'JPY', 1000],
            'jpy rounds up' => [1000.5, 'JPY', 1001],
            'jpy negative' => [-1000.0, 'JPY', -1000],
            'krw whole amount' => [50000.0, 'KRW', 50000],
            'vnd whole amount' => [25000.0, 'VND', 25000],

            // ISO 4217 exponent 3.
            'kwd three decimals' => [1.005, 'KWD', 1005],
            'kwd pads to three decimals' => [19.99, 'KWD', 19990],
            'kwd sub-unit rounds up' => [0.0005, 'KWD', 1],
            'kwd negative' => [-1.005, 'KWD', -1005],
            'bhd three decimals' => [12.345, 'BHD', 12345],

            'eur defaults to two' => [19.99, 'EUR', 1999],
            'unknown code defaults to two' => [19.99, 'XYZ', 1999],
            'lowercase currency code' => [19.99, 'usd', 1999],
        ];
    }
}
