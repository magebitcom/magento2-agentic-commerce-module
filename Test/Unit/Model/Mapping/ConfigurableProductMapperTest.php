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
use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magebit\AgenticCommerce\Model\Config\ProductFeedMapping;
use Magebit\AgenticCommerce\Model\Data\Feed\Product as FeedProduct;
use Magebit\AgenticCommerce\Model\Mapping\ConfigurableProductMapper;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

/**
 * A variant row carries the mapped columns plus a pair for every option its parent varies by. The
 * option columns used to be merged in on every pass of the mapping loop, which both repeated the
 * work and left them sitting in the middle of the row.
 */
class ConfigurableProductMapperTest extends TestCase
{
    /**
     * @return void
     */
    public function testTheOptionColumnsComeAfterTheMappedColumns(): void
    {
        $columns = array_keys($this->mapChild()->getData());

        $this->assertSame(
            ['id', 'price', 'item_group_id', 'Custom_variant1_category', 'Custom_variant1_option'],
            $columns
        );
    }

    /**
     * @return void
     */
    public function testTheOptionColumnsAreAddedOnce(): void
    {
        $data = $this->mapChild()->getData();
        $optionColumns = array_filter(
            array_keys($data),
            static fn (string $column): bool => str_starts_with($column, 'Custom_variant')
        );

        $this->assertCount(2, $optionColumns);
    }

    /**
     * @return void
     */
    public function testTheOptionCarriesItsLabelAndTheChosenValue(): void
    {
        $data = $this->mapChild()->getData();

        $this->assertSame('Size', $data['Custom_variant1_category']);
        $this->assertSame('XS', $data['Custom_variant1_option']);
    }

    /**
     * Built without the constructor so the test does not stand up the configurable product type,
     * then given only what a child row is made from.
     *
     * @return FeedProduct
     */
    private function mapChild(): FeedProduct
    {
        $mapping = [
            ['source_attribute' => $this->source('MH01-XS-Black'), 'target_attribute' => 'id'],
            ['source_attribute' => $this->source('52.00 USD'), 'target_attribute' => 'price'],
            ['source_attribute' => $this->source('MH01'), 'target_attribute' => 'item_group_id'],
        ];

        $feedMapping = $this->createMock(ProductFeedMapping::class);
        $feedMapping->method('getMappingsForTypes')->willReturn($mapping);

        $factory = $this->createMock(FeedProductInterfaceFactory::class);
        $factory->method('create')->willReturnCallback(
            static fn (array $args = []): FeedProduct => new FeedProduct($args['data'] ?? [])
        );

        $mapper = $this->getMockBuilder(ConfigurableProductMapper::class)
            ->setConstructorArgs([$factory, $feedMapping])
            ->onlyMethods(['addVariantAttributes'])
            ->getMock();

        $mapper->method('addVariantAttributes')->willReturn([
            'Custom_variant1_category' => 'Size',
            'Custom_variant1_option' => 'XS',
        ]);

        /** @var FeedProduct $mapped */
        $mapped = $mapper->mapChildProduct($this->product(), $this->product());

        return $mapped;
    }

    /**
     * @param string $value What this column reports
     * @return SourceInterface
     */
    private function source(string $value): SourceInterface
    {
        $source = $this->createMock(SourceInterface::class);
        $source->method('getValue')->willReturn($value);

        return $source;
    }

    /**
     * @return Product
     */
    private function product(): Product
    {
        return $this->createMock(Product::class);
    }
}
