<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit;

use JsonException;
use PHPUnit\Framework\TestCase;

/**
 * Validates the golden ACP feed fixtures against the vendored schema bundle.
 */
class FeedFixtureTest extends TestCase
{
    use SchemaAssert;

    private const METADATA_SCHEMA = 'schema.feed.json#/$defs/FeedMetadata';
    private const PRODUCTS_SCHEMA = 'schema.feed.json#/$defs/ProductsResponse';

    /**
     * @return void
     * @throws JsonException
     */
    public function testFeedMetadataMatchesSpec(): void
    {
        $this->assertMatchesSchema(self::loadFixtureObject('feed.create.201.json'), self::METADATA_SCHEMA);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testFeedProductsMatchSpec(): void
    {
        $this->assertMatchesSchema(self::loadFixtureObject('feed.products.200.json'), self::PRODUCTS_SCHEMA);
    }

    /**
     * The id has to name the country back, because nothing is stored: a feed is the store's catalogue
     * for one target country and the id is the only record of which.
     *
     * @return void
     * @throws JsonException
     */
    public function testTheFeedIdEncodesItsTargetCountry(): void
    {
        $metadata = self::loadFixture('feed.create.201.json');

        $this->assertSame('feed_us', $metadata['id']);
        $this->assertSame('US', $metadata['target_country']);
    }

    /**
     * An agent buys a variant, so every product carries at least one — the spec requires it.
     *
     * @return void
     * @throws JsonException
     */
    public function testEveryFeedProductHasAVariant(): void
    {
        $products = self::loadFixture('feed.products.200.json')['products'];

        $this->assertNotEmpty($products);

        foreach ($products as $product) {
            $this->assertNotEmpty($product['variants'], $product['id'] . ' has a variant');
            $this->assertArrayHasKey('availability', $product['variants'][0]);
        }
    }

    /**
     * A price with no currency cannot be compared to anything, so both halves are always present.
     *
     * @return void
     * @throws JsonException
     */
    public function testEveryPriceCarriesItsCurrency(): void
    {
        foreach (self::loadFixture('feed.products.200.json')['products'] as $product) {
            foreach ($product['variants'] as $variant) {
                $this->assertArrayHasKey('currency', $variant['price']);
                $this->assertIsInt($variant['price']['amount']);
            }
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testAnUnknownFeedIsAnError(): void
    {
        $this->assertSame('feed_not_found', self::loadFixture('feed.products.404.json')['code']);
    }
}
