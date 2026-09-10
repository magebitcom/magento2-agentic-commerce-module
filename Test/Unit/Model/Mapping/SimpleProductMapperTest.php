<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\Mapping;

use Magebit\AgenticCommerce\Api\Data\FeedProductInterfaceFactory;
use Magebit\AgenticCommerce\Api\Mapping\FormatterInterface;
use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magebit\AgenticCommerce\Model\Config\ProductFeedMapping;
use Magebit\AgenticCommerce\Model\Data\Feed\Product as FeedProduct;
use Magebit\AgenticCommerce\Model\Mapping\SimpleProductMapper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

/**
 * How a mapped column gets its value. The mapper reads it from a source, then hands it to a
 * formatter; both steps used to be done twice, and the second read dropped the parent product.
 */
class SimpleProductMapperTest extends TestCase
{
    /**
     * The parent is where a variant's group id comes from, so a source that is not given it returns
     * nothing and the column comes out empty.
     *
     * @return void
     */
    public function testTheParentProductReachesTheSource(): void
    {
        $seen = [];

        $source = $this->createMock(SourceInterface::class);
        $source->method('getValue')->willReturnCallback(
            function (ProductInterface $product, ?ProductInterface $parent = null) use (&$seen): ?string {
                $seen[] = $parent?->getSku();

                return $parent?->getSku();
            }
        );

        $value = $this->map(['source_attribute' => $source, 'target_attribute' => 'item_group_id']);

        $this->assertSame(['MH01'], $seen);
        $this->assertSame('MH01', $value);
    }

    /**
     * @return void
     */
    public function testTheFormatterIsGivenTheValueTheSourceReturned(): void
    {
        $source = $this->createMock(SourceInterface::class);
        $source->method('getValue')->willReturn('in stock');

        $formatter = $this->createMock(FormatterInterface::class);
        $formatter->method('format')->willReturnCallback(
            static fn (ProductInterface $product, mixed $value): string => strtoupper((string) $value)
        );

        $value = $this->map([
            'source_attribute' => $source,
            'target_attribute' => 'availability',
            'formatter' => $formatter,
        ]);

        $this->assertSame('IN STOCK', $value);
    }

    /**
     * Reading twice meant every source ran twice per column, which for a source that loads anything
     * doubled the work of the whole export.
     *
     * @return void
     */
    public function testTheSourceIsReadOncePerColumn(): void
    {
        $source = $this->createMock(SourceInterface::class);
        $source->expects($this->once())->method('getValue')->willReturn('x');

        $this->map(['source_attribute' => $source, 'target_attribute' => 'brand']);
    }

    /**
     * @return void
     */
    public function testAPlainAttributeNameIsReadFromTheProduct(): void
    {
        $value = $this->map(['source_attribute' => 'sku', 'target_attribute' => 'id']);

        $this->assertSame('MH01-XS-Black', $value);
    }

    /**
     * @param array<string, mixed> $mapping One column's configuration
     * @return mixed The value that column would carry
     */
    private function map(array $mapping): mixed
    {
        $factory = $this->createMock(FeedProductInterfaceFactory::class);
        $factory->method('create')->willReturnCallback(
            static fn (array $args = []): FeedProduct => new FeedProduct($args['data'] ?? [])
        );

        $mapper = new SimpleProductMapper($factory, $this->createMock(ProductFeedMapping::class));

        return $mapper->mapAttribute($this->child(), $mapping, $this->parent());
    }

    /**
     * @return Product
     */
    private function child(): Product
    {
        $product = $this->createMock(Product::class);
        $product->method('getSku')->willReturn('MH01-XS-Black');
        $product->method('getData')->willReturnCallback(
            static fn (string $key = '', mixed $index = null): mixed => $key === 'sku' ? 'MH01-XS-Black' : null
        );

        return $product;
    }

    /**
     * @return Product
     */
    private function parent(): Product
    {
        $product = $this->createMock(Product::class);
        $product->method('getSku')->willReturn('MH01');

        return $product;
    }
}
