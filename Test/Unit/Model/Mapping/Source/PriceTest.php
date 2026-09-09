<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\Mapping\Source;

use Magebit\AgenticCommerce\Model\Mapping\Source\Price;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

/**
 * A bundle or a grouped product holds no price of its own. Reading the price field gave a bundle
 * zero, so the feed offered a paid bundle for nothing, and gave a grouped product nothing at all.
 */
class PriceTest extends TestCase
{
    /**
     * @return void
     */
    public function testAProductWithItsOwnPriceUsesIt(): void
    {
        $this->assertSame(32.0, $this->price(['price' => '32.000000', 'min_price' => '20.000000']));
    }

    /**
     * @return void
     */
    public function testABundlePricedAtZeroFallsBackToWhatItActuallyCosts(): void
    {
        $this->assertSame(61.0, $this->price(['price' => '0.000000', 'min_price' => '61.000000']));
    }

    /**
     * @return void
     */
    public function testAGroupedProductWithNoPriceFallsBackToWhatItActuallyCosts(): void
    {
        $this->assertSame(14.0, $this->price(['price' => null, 'min_price' => '14.000000']));
    }

    /**
     * With no price anywhere the column is left empty, which is honest. A zero would read as free.
     *
     * @return void
     */
    public function testAProductWithNoPriceAnywhereReportsNothing(): void
    {
        $this->assertNull($this->price(['price' => null, 'min_price' => null]));
    }

    /**
     * @param array<string, string|null> $data The price fields the product carries
     * @return float|null
     */
    private function price(array $data): ?float
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->willReturnCallback(
            static fn (string $key = '', mixed $index = null): mixed => $data[$key] ?? null
        );

        return (new Price())->getValue($product);
    }
}
