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

use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentOptionShippingInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterfaceFactory;
use Magebit\AcpSpec\Data\AgenticCheckout\FulfillmentOptionShipping;
use Magebit\AcpSpec\Data\AgenticCheckout\Total;
use Magebit\AgenticCommerce\Model\Convert\CartToFulfillmentOptions;
use Magebit\AgenticCore\Model\Fulfillment\ShippingOption;
use Magebit\AgenticCore\Model\Fulfillment\ShippingOptionResolver;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class CartToFulfillmentOptionsTest extends TestCase
{
    /**
     * @return void
     */
    public function testAnOptionCarriesTheTypedTotalBreakdown(): void
    {
        $options = $this->convert([new ShippingOption('flatrate_flatrate', 'Fixed', 'Flat Rate', 'Flat Rate', 500, 105, 605)]);

        $this->assertCount(1, $options);
        $this->assertSame('flatrate_flatrate', $options[0]->getId());
        $this->assertSame('Flat Rate', $options[0]->getTitle());

        $this->assertSame(
            [
                TotalInterface::TYPE_SUBTOTAL => 500,
                TotalInterface::TYPE_TAX => 105,
                TotalInterface::TYPE_TOTAL => 605,
            ],
            $this->amountsByType($options[0]->getTotals())
        );
    }

    /**
     * A free method still has a subtotal and a total; only a zero tax line is noise.
     *
     * @return void
     */
    public function testAZeroTaxLineIsOmittedButTheZeroTotalIsNot(): void
    {
        $options = $this->convert([new ShippingOption('free_free', 'Free', null, 'Free Shipping', 0, 0, 0)]);

        $this->assertSame(
            [TotalInterface::TYPE_SUBTOTAL => 0, TotalInterface::TYPE_TOTAL => 0],
            $this->amountsByType($options[0]->getTotals())
        );
    }

    /**
     * The resolver drops rates the carrier rejected, so the converter offers nothing for them.
     *
     * @return void
     */
    public function testNothingIsOfferedWhenTheResolverReturnsNothing(): void
    {
        $this->assertSame([], $this->convert([]));
    }

    /**
     * @param ShippingOption[] $resolved
     * @return \Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentOptionShippingInterface[]
     */
    private function convert(array $resolved): array
    {
        $resolver = $this->createMock(ShippingOptionResolver::class);
        $resolver->method('resolve')->willReturn($resolved);

        $optionFactory = $this->createMock(FulfillmentOptionShippingInterfaceFactory::class);
        $optionFactory->method('create')
            ->willReturnCallback(static fn (): FulfillmentOptionShipping => new FulfillmentOptionShipping());

        $totalFactory = $this->createMock(TotalInterfaceFactory::class);
        $totalFactory->method('create')->willReturnCallback(static fn (): Total => new Total());

        $converter = new CartToFulfillmentOptions($optionFactory, $totalFactory, $resolver);

        $cart = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->onlyMethods([])->getMock();

        return $converter->execute($cart);
    }

    /**
     * @param TotalInterface[] $totals
     * @return array<string, int>
     */
    private function amountsByType(array $totals): array
    {
        $amounts = [];

        foreach ($totals as $total) {
            $amounts[(string) $total->getType()] = (int) $total->getAmount();
        }

        return $amounts;
    }
}
