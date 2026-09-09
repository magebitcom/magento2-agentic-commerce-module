<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\Convert;

use Magebit\AcpSpec\Api\AgenticCheckout\AppliedDiscountInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\CouponInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscountsResponseInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscountsResponseInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\RejectedDiscountInterfaceFactory;
use Magebit\AcpSpec\Data\AgenticCheckout\AppliedDiscount;
use Magebit\AcpSpec\Data\AgenticCheckout\Coupon;
use Magebit\AcpSpec\Data\AgenticCheckout\DiscountsResponse;
use Magebit\AcpSpec\Data\AgenticCheckout\RejectedDiscount;
use Magebit\AgenticCore\Model\Money\MinorUnits;
use Magebit\AgenticCommerce\Model\Convert\CartToDiscounts;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class CartToDiscountsTest extends TestCase
{
    /**
     * @return void
     */
    public function testAnAppliedCodeIsReported(): void
    {
        $discounts = $this->convert(couponCode: 'SAVE20', discountAmount: -5.00, submitted: ['SAVE20']);

        $this->assertSame(['SAVE20'], $discounts->getCodes());
        $this->assertCount(1, $discounts->getApplied());
        $this->assertSame('SAVE20', $discounts->getApplied()[0]->getCode());
        $this->assertSame([], $discounts->getRejected() ?? []);
    }

    /**
     * The spec requires a positive amount on an applied discount; Magento carries the discount as a
     * negative on the quote.
     *
     * @return void
     */
    public function testTheAppliedAmountIsPositiveMinorUnits(): void
    {
        $discounts = $this->convert(couponCode: 'SAVE20', discountAmount: -5.00, submitted: ['SAVE20']);

        $this->assertSame(500, $discounts->getApplied()[0]->getAmount());
    }

    /**
     * @return void
     */
    public function testACodeMagentoDidNotKeepIsRejected(): void
    {
        $discounts = $this->convert(couponCode: null, discountAmount: 0.0, submitted: ['NOPE']);

        $this->assertSame([], $discounts->getApplied() ?? []);
        $this->assertCount(1, $discounts->getRejected());
        $this->assertSame('NOPE', $discounts->getRejected()[0]->getCode());
        $this->assertSame(CartToDiscounts::REASON_INVALID, $discounts->getRejected()[0]->getReason());
    }

    /**
     * Magento keeps one coupon per quote, so a second code is rejected as a disallowed combination
     * rather than silently dropped.
     *
     * @return void
     */
    public function testASecondCodeIsRejectedAsADisallowedCombination(): void
    {
        $discounts = $this->convert(
            couponCode: 'SAVE20',
            discountAmount: -5.00,
            submitted: ['SAVE20', 'ALSOTHIS']
        );

        $this->assertCount(1, $discounts->getApplied());
        $this->assertCount(1, $discounts->getRejected());
        $this->assertSame('ALSOTHIS', $discounts->getRejected()[0]->getCode());
        $this->assertSame(
            CartToDiscounts::REASON_COMBINATION_DISALLOWED,
            $discounts->getRejected()[0]->getReason()
        );
    }

    /**
     * An automatic cart rule has no code, and the spec omits `code` for those rather than inventing one.
     *
     * @return void
     */
    public function testAnAutomaticDiscountHasNoCode(): void
    {
        $discounts = $this->convert(couponCode: null, discountAmount: -3.00, submitted: []);

        $this->assertCount(1, $discounts->getApplied());
        $this->assertNull($discounts->getApplied()[0]->getCode());
        $this->assertSame(300, $discounts->getApplied()[0]->getAmount());
    }

    /**
     * @return void
     */
    public function testNoDiscountAtAllReportsNothing(): void
    {
        $this->assertNull($this->convert(couponCode: null, discountAmount: 0.0, submitted: []));
    }

    /**
     * @param string|null $couponCode
     * @param float $discountAmount
     * @param string[] $submitted
     * @return DiscountsResponse|null
     */
    private function convert(?string $couponCode, float $discountAmount, array $submitted): ?DiscountsResponse
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCurrency'])
            ->addMethods(['getCouponCode', 'getSubtotalWithDiscount', 'getSubtotal'])
            ->getMock();
        $quote->method('getCouponCode')->willReturn($couponCode);
        $quote->method('getSubtotal')->willReturn(50.0);
        $quote->method('getSubtotalWithDiscount')->willReturn(50.0 + $discountAmount);
        $quote->method('getCurrency')->willReturn(null);

        /** @var DiscountsResponse|null $result */
        $result = $this->converter()->execute($quote, $submitted);

        return $result;
    }

    /**
     * @return CartToDiscounts
     */
    private function converter(): CartToDiscounts
    {
        return new CartToDiscounts(
            $this->factoryFor(DiscountsResponseInterfaceFactory::class, DiscountsResponse::class),
            $this->factoryFor(AppliedDiscountInterfaceFactory::class, AppliedDiscount::class),
            $this->factoryFor(RejectedDiscountInterfaceFactory::class, RejectedDiscount::class),
            $this->factoryFor(CouponInterfaceFactory::class, Coupon::class),
            new MinorUnits()
        );
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
