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

use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterfaceFactory;
use Magebit\AcpSpec\Data\AgenticCheckout\Total;
use Magebit\AgenticCore\Model\Money\MinorUnits;
use Magebit\AgenticCore\Model\Total\TypeLabel;
use Magebit\AgenticCommerce\Model\Convert\CartToTotals;
use Magento\Quote\Model\Quote\Address\Total as QuoteTotal;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class CartToTotalsTest extends TestCase
{
    /**
     * Magento's own total codes, which are not the spec's vocabulary.
     */
    private const MAGENTO_TOTALS = [
        ['subtotal', 'Subtotal', 64.00],
        ['shipping', 'Shipping & Handling', 10.00],
        ['tax', 'Tax', 0.0],
        ['grand_total', 'Grand Total', 74.00],
    ];

    /**
     * Magento calls it `shipping`; the spec enum has no such member and calls it `fulfillment`.
     *
     * @return void
     */
    public function testShippingIsReportedAsFulfillment(): void
    {
        $this->assertSame(TotalInterface::TYPE_FULFILLMENT, $this->convert()[1]->getType());
    }

    /**
     * Magento calls it `grand_total`; the spec enum calls it `total`.
     *
     * @return void
     */
    public function testGrandTotalIsReportedAsTotal(): void
    {
        $this->assertSame(TotalInterface::TYPE_TOTAL, $this->convert()[3]->getType());
    }

    /**
     * @return void
     */
    public function testEveryTypeIsInTheSpecEnum(): void
    {
        $allowed = [
            TotalInterface::TYPE_ITEMS_BASE_AMOUNT,
            TotalInterface::TYPE_ITEMS_DISCOUNT,
            TotalInterface::TYPE_SUBTOTAL,
            TotalInterface::TYPE_DISCOUNT,
            TotalInterface::TYPE_FULFILLMENT,
            TotalInterface::TYPE_TAX,
            TotalInterface::TYPE_FEE,
            TotalInterface::TYPE_GIFT_WRAP,
            TotalInterface::TYPE_TIP,
            TotalInterface::TYPE_STORE_CREDIT,
            TotalInterface::TYPE_TOTAL,
            TotalInterface::TYPE_AMOUNT_REFUNDED,
        ];

        foreach ($this->convert() as $total) {
            $this->assertContains($total->getType(), $allowed);
        }
    }

    /**
     * `display_text` is required, so a total without one is not a partial payload but an invalid one.
     *
     * @return void
     */
    public function testEveryTotalCarriesDisplayText(): void
    {
        foreach ($this->convert() as $total) {
            $this->assertNotSame('', (string) $total->getDisplayText());
        }
    }

    /**
     * A code the spec has no member for is dropped rather than emitted as an invalid type. It stays
     * inside the grand total either way.
     *
     * @return void
     */
    public function testAnUnmappableCodeIsDropped(): void
    {
        $totals = $this->convert([['weee', 'FPT', 5.00], ['grand_total', 'Grand Total', 79.00]]);

        $this->assertCount(1, $totals);
        $this->assertSame(TotalInterface::TYPE_TOTAL, $totals[0]->getType());
    }

    /**
     * A total Magento left untitled still needs a label, derived from the type.
     *
     * @return void
     */
    public function testFallsBackToALabelDerivedFromTheType(): void
    {
        $totals = $this->convert([['grand_total', '', 74.00]]);

        $this->assertSame('Total', $totals[0]->getDisplayText());
    }

    /**
     * @param array<int, array{0: string, 1: string, 2: float}>|null $rows
     * @return TotalInterface[]
     */
    private function convert(?array $rows = null): array
    {
        $factory = $this->createMock(TotalInterfaceFactory::class);
        $factory->method('create')->willReturnCallback(static fn (): Total => new Total());

        $cartTotals = [];

        foreach ($rows ?? self::MAGENTO_TOTALS as [$code, $title, $value]) {
            // A real Total: getCode/getTitle/getValue are magic DataObject accessors, so mocking them
            // is not possible and setting the data is both simpler and closer to the real thing.
            $total = new QuoteTotal();
            $total->setData('code', $code);
            $total->setData('title', $title);
            $total->setData('value', $value);
            $cartTotals[] = $total;
        }

        $cart = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTotals', 'getCurrency'])
            ->getMock();
        $cart->method('getTotals')->willReturn($cartTotals);
        $cart->method('getCurrency')->willReturn(null);

        return (new CartToTotals($factory, new MinorUnits(), new TypeLabel(), [
            'subtotal' => TotalInterface::TYPE_SUBTOTAL,
            'shipping' => TotalInterface::TYPE_FULFILLMENT,
            'tax' => TotalInterface::TYPE_TAX,
            'discount' => TotalInterface::TYPE_DISCOUNT,
            'grand_total' => TotalInterface::TYPE_TOTAL,
        ]))->execute($cart);
    }
}
