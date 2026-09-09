<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\Convert\Feed;

use Magebit\AcpSpec\Api\Feed\AvailabilityInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\DescriptionInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\MediaInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\PriceInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\ProductInterface;
use Magebit\AcpSpec\Api\Feed\ProductInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\VariantInterfaceFactory;
use Magebit\AcpSpec\Data\Feed\Availability;
use Magebit\AcpSpec\Data\Feed\Description;
use Magebit\AcpSpec\Data\Feed\Media;
use Magebit\AcpSpec\Data\Feed\Price;
use Magebit\AcpSpec\Data\Feed\Product;
use Magebit\AcpSpec\Data\Feed\Variant;
use Magebit\AgenticCommerce\Model\Convert\Feed\ProductToFeedProduct;
use Magebit\AgenticCore\Model\Stock\Availability as StockAvailability;
use Magebit\AgenticCore\Model\Money\MinorUnits;
use Magento\Catalog\Model\Product as MagentoProduct;
use PHPUnit\Framework\TestCase;

class ProductToFeedProductTest extends TestCase
{
    /**
     * @return void
     */
    public function testCarriesTheRequiredFields(): void
    {
        $product = $this->convert();

        $this->assertSame('feed-sku', $product->getId());
        $this->assertNotEmpty($product->getVariants());
    }

    /**
     * The spec requires at least one variant, so a product with no children is its own.
     *
     * @return void
     */
    public function testAProductWithNoChildrenIsItsOwnVariant(): void
    {
        $variants = $this->convert()->getVariants();

        $this->assertCount(1, $variants);
        $this->assertSame('feed-sku', $variants[0]->getId());
        $this->assertSame('A Feed Product', $variants[0]->getTitle());
    }

    /**
     * @return void
     */
    public function testPricesAreMinorUnitsWithACurrency(): void
    {
        $price = $this->convert()->getVariants()[0]->getPrice();

        $this->assertSame(1500, $price->getAmount());
        $this->assertSame('USD', $price->getCurrency());
    }

    /**
     * A list price the same as the selling price says nothing, so it is left out.
     *
     * @return void
     */
    public function testTheListPriceIsOmittedWhenItMatchesTheSellingPrice(): void
    {
        $this->assertNull($this->convert(price: 15.0, finalPrice: 15.0)->getVariants()[0]->getListPrice());
    }

    /**
     * @return void
     */
    public function testTheListPriceIsReportedWhenTheItemIsDiscounted(): void
    {
        $variant = $this->convert(price: 20.0, finalPrice: 15.0)->getVariants()[0];

        $this->assertSame(2000, $variant->getListPrice()->getAmount());
        $this->assertSame(1500, $variant->getPrice()->getAmount());
    }

    /**
     * @return void
     */
    public function testAvailabilityComesFromStockNotTheProduct(): void
    {
        $this->assertTrue($this->convert()->getVariants()[0]->getAvailability()->getAvailable());
        $this->assertSame(
            ProductToFeedProduct::STATUS_OUT_OF_STOCK,
            $this->convert(isSalable: false)->getVariants()[0]->getAvailability()->getStatus()
        );
    }

    /**
     * @return void
     */
    public function testTheDescriptionIsATypedObjectWithAPlainForm(): void
    {
        $this->assertSame('Plain words.', $this->convert()->getDescription()->getPlain());
    }

    /**
     * @param float $price
     * @param float $finalPrice
     * @param bool $isSalable
     * @return ProductInterface
     */
    private function convert(float $price = 15.0, float $finalPrice = 15.0, bool $isSalable = true): ProductInterface
    {
        $product = $this->getMockBuilder(MagentoProduct::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSku', 'getName', 'getTypeId', 'getFinalPrice', 'getPrice', 'getData'])
            ->getMock();
        $product->method('getSku')->willReturn('feed-sku');
        $product->method('getName')->willReturn('A Feed Product');
        $product->method('getTypeId')->willReturn('simple');
        $product->method('getFinalPrice')->willReturn($finalPrice);
        $product->method('getPrice')->willReturn($price);
        $product->method('getData')->willReturnCallback(
            static fn (string $key): mixed => $key === 'description' ? 'Plain words.' : null
        );

        $stock = $this->createMock(StockAvailability::class);
        $stock->method('isSalable')->willReturn($isSalable);

        $converter = new ProductToFeedProduct(
            $this->factoryFor(ProductInterfaceFactory::class, Product::class),
            $this->factoryFor(VariantInterfaceFactory::class, Variant::class),
            $this->factoryFor(DescriptionInterfaceFactory::class, Description::class),
            $this->factoryFor(PriceInterfaceFactory::class, Price::class),
            $this->factoryFor(AvailabilityInterfaceFactory::class, Availability::class),
            $this->factoryFor(MediaInterfaceFactory::class, Media::class),
            new MinorUnits(),
            $stock
        );

        return $converter->execute($product, 'USD');
    }

    /**
     * @param class-string $factoryClass
     * @param class-string $concrete
     * @return mixed
     */
    private function factoryFor(string $factoryClass, string $concrete): mixed
    {
        $factory = $this->createMock($factoryClass);
        $factory->method('create')->willReturnCallback(
            static fn (array $args = []): object => new $concrete($args['data'] ?? [])
        );

        return $factory;
    }
}
