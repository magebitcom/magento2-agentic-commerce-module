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
 * Validates the golden ACP cart fixtures against the vendored schema bundle.
 */
class CartFixtureTest extends TestCase
{
    use SchemaAssert;

    private const CART_SCHEMA = 'schema.cart.json#/$defs/Cart';

    /**
     * @return array<string, array{0: string}>
     */
    public static function cartFixtureProvider(): array
    {
        return [
            'create 200' => ['cart.create.200.json'],
            'get 200' => ['cart.get.200.json'],
            'update 200' => ['cart.update.200.json'],
            'cancel 200' => ['cart.cancel.200.json'],
        ];
    }

    /**
     * @dataProvider cartFixtureProvider
     * @param string $fixture
     * @return void
     * @throws JsonException
     */
    public function testCartMatchesSpec(string $fixture): void
    {
        $this->assertMatchesSchema(self::loadFixtureObject($fixture), self::CART_SCHEMA);
    }

    /**
     * `Cart` is `additionalProperties: false` and is a smaller resource than a session — none of these
     * belong on it.
     *
     * @dataProvider cartFixtureProvider
     * @param string $fixture
     * @return void
     * @throws JsonException
     */
    public function testACartCarriesNoSessionOnlyFields(string $fixture): void
    {
        $cart = self::loadFixture($fixture);

        foreach (['status', 'payment_provider', 'fulfillment_options', 'capabilities', 'order'] as $field) {
            $this->assertArrayNotHasKey($field, $cart, sprintf('"%s" is not a cart field', $field));
        }
    }

    /**
     * @dataProvider cartFixtureProvider
     * @param string $fixture
     * @return void
     * @throws JsonException
     */
    public function testEveryRequiredCartFieldIsPresent(string $fixture): void
    {
        $cart = self::loadFixture($fixture);

        foreach (['id', 'line_items', 'currency', 'totals'] as $field) {
            $this->assertArrayHasKey($field, $cart);
        }

        $this->assertNotEmpty($cart['line_items']);
    }

    /**
     * An update is a full replacement, so the submitted quantity is what the cart reports back.
     *
     * @return void
     * @throws JsonException
     */
    public function testAnUpdateReplacesTheCart(): void
    {
        $this->assertSame(5, self::loadFixture('cart.update.200.json')['line_items'][0]['quantity']);
    }

    /**
     * A canceled cart is gone rather than a cart in a canceled state, so a later read is an error.
     *
     * @return void
     * @throws JsonException
     */
    public function testReadingACanceledCartIsAnError(): void
    {
        $payload = self::loadFixture('cart.get.404.json');

        $this->assertSame('cart_not_found', $payload['code']);
    }
}
