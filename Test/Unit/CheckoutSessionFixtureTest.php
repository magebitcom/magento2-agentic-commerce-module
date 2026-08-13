<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit;

use JsonException;
use PHPUnit\Framework\TestCase;

/**
 * Validates the golden ACP fixtures against the vendored schema bundle.
 */
class CheckoutSessionFixtureTest extends TestCase
{
    use SchemaAssert;

    private const SESSION_SCHEMA = 'schema.agentic_checkout.json#/$defs/CheckoutSession';
    private const ORDER_SCHEMA = 'schema.agentic_checkout.json#/$defs/Order';
    private const COMPLETE_FIXTURE = 'checkout_session.complete.200.json';
    private const DISCOUNTED_FIXTURE = 'checkout_session.create.discounted.200.json';
    private const DISCOUNT_SCHEMA = 'schema.discount.json#/$defs/checkout_with_discount';

    /**
     * @return array<string, array{0: string}>
     */
    public static function sessionFixtureProvider(): array
    {
        return [
            'create 200' => ['checkout_session.create.200.json'],
            'get 200' => ['checkout_session.get.200.json'],
            'update 200' => ['checkout_session.update.200.json'],
            'cancel 200' => ['checkout_session.cancel.200.json'],
        ];
    }

    /**
     * @dataProvider sessionFixtureProvider
     * @param string $fixture
     * @return void
     * @throws JsonException
     */
    public function testSessionFixtureHasExpectedEnvelope(string $fixture): void
    {
        $payload = self::loadFixture($fixture);

        foreach (['id', 'line_items', 'totals', 'currency', 'status', 'links', 'messages'] as $key) {
            $this->assertArrayHasKey($key, $payload, sprintf('"%s" missing from %s', $key, $fixture));
        }

        $this->assertSame('checkout_session_placeholder_0001', $payload['id'], 'Session id must stay scrubbed.');
        $this->assertNotEmpty($payload['line_items']);
    }

    /**
     * @dataProvider sessionFixtureProvider
     * @param string $fixture
     * @return void
     * @throws JsonException
     */
    public function testSessionFixtureMatchesSpec(string $fixture): void
    {
        $this->assertMatchesSchema(self::loadFixtureObject($fixture), self::SESSION_SCHEMA);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testCompletedSessionMatchesSpec(): void
    {
        $this->assertMatchesSchema(
            self::loadFixtureObject(self::COMPLETE_FIXTURE),
            'schema.agentic_checkout.json#/$defs/CheckoutSessionWithOrder'
        );
    }

    /**
     * Every total needs a type from the spec's enum and a label, at cart level and inside each line item
     * and fulfillment option. Magento's own codes are not that vocabulary.
     *
     * @dataProvider sessionFixtureProvider
     * @param string $fixture
     * @return void
     * @throws JsonException
     */
    public function testEveryTotalIsLabelledAndTyped(string $fixture): void
    {
        $payload = self::loadFixture($fixture);
        $groups = [$payload['totals']];

        foreach ($payload['line_items'] as $lineItem) {
            $groups[] = $lineItem['totals'];
        }

        foreach ($payload['fulfillment_options'] ?? [] as $option) {
            $groups[] = $option['totals'];
        }

        foreach ($groups as $totals) {
            foreach ($totals as $total) {
                $this->assertNotEmpty($total['display_text'], 'every total carries a label');
                $this->assertNotContains($total['type'], ['shipping', 'grand_total'], 'Magento codes are mapped');
            }
        }
    }

    /**
     * The spec returns CheckoutSessionWithOrder on completion, and the order it carries requires an id,
     * the session id and a permalink.
     *
     * @return void
     * @throws JsonException
     */
    public function testCompletedSessionCarriesAConformantOrder(): void
    {
        $payload = self::loadFixture(self::COMPLETE_FIXTURE);

        $this->assertMatchesSchema(self::loadFixtureObject(self::COMPLETE_FIXTURE)->order, self::ORDER_SCHEMA);
        $this->assertSame('completed', $payload['status']);
        $this->assertSame($payload['id'], $payload['order']['checkout_session_id']);
        $this->assertStringContainsString($payload['id'], $payload['order']['permalink_url']);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testADiscountedSessionMatchesTheDiscountExtension(): void
    {
        $this->assertMatchesSchema(self::loadFixtureObject(self::DISCOUNTED_FIXTURE), self::DISCOUNT_SCHEMA);
    }

    /**
     * Magento holds one coupon per quote, so a second code is reported rejected rather than dropped
     * without explanation.
     *
     * @return void
     * @throws JsonException
     */
    public function testASecondDiscountCodeIsRejectedWithAReason(): void
    {
        $discounts = self::loadFixture(self::DISCOUNTED_FIXTURE)['discounts'];

        $this->assertCount(2, $discounts['codes']);
        $this->assertCount(1, $discounts['applied']);
        $this->assertCount(1, $discounts['rejected']);
        $this->assertSame('discount_code_combination_disallowed', $discounts['rejected'][0]['reason']);
    }

    /**
     * The spec wants a positive amount on an applied discount; Magento carries it as a reduction.
     *
     * @return void
     * @throws JsonException
     */
    public function testTheAppliedDiscountAmountIsPositive(): void
    {
        $applied = self::loadFixture(self::DISCOUNTED_FIXTURE)['discounts']['applied'][0];

        $this->assertGreaterThan(0, $applied['amount']);
        $this->assertSame($applied['code'], $applied['coupon']['id']);
    }

    /**
     * An agent learns which extra fields to expect from the declaration rather than by inspecting the
     * payload, so every extension the session serves is named here.
     *
     * @dataProvider sessionFixtureProvider
     * @param string $fixture
     * @return void
     * @throws JsonException
     */
    public function testActiveExtensionsAreDeclared(string $fixture): void
    {
        $extensions = self::loadFixture($fixture)['capabilities']['extensions'] ?? [];

        $this->assertSame(['discount'], array_column($extensions, 'name'));
        $this->assertContains('$.CheckoutSession.discounts', $extensions[0]['extends']);
    }
}
