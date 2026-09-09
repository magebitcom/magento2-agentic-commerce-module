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

use Magebit\AcpSpec\Api\AgenticCheckout\AddressInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterfaceFactory;
use Magebit\AcpSpec\Data\AgenticCheckout\Address;
use Magebit\AcpSpec\Data\AgenticCheckout\FulfillmentDetails;
use Magebit\AgenticCommerce\Model\Convert\CartToFulfillmentDetails;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartToFulfillmentDetailsTest extends TestCase
{
    /**
     * @var AddressInterfaceFactory&MockObject
     */
    private AddressInterfaceFactory $addressFactory;

    /**
     * @var CartToFulfillmentDetails
     */
    private CartToFulfillmentDetails $converter;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->addressFactory = $this->getMockBuilder(AddressInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->addressFactory->method('create')->willReturnCallback(static fn (): Address => new Address());

        $detailsFactory = $this->getMockBuilder(FulfillmentDetailsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $detailsFactory->method('create')->willReturnCallback(static fn (): FulfillmentDetails => new FulfillmentDetails());

        $this->converter = new CartToFulfillmentDetails($detailsFactory, $this->addressFactory);
    }

    /**
     * An address the buyer never supplied must be omitted, not emitted as an object full of nulls.
     *
     * @return void
     */
    public function testEmptyQuoteAddressYieldsNull(): void
    {
        $cart = $this->createCart([]);

        $this->assertNull($this->converter->execute($cart));
    }

    /**
     * A shipping-estimate quote carries country and postcode only; that must not fatal.
     *
     * @dataProvider partialAddressProvider
     * @param array<string, mixed> $data
     * @return void
     */
    public function testPartialAddressYieldsNull(array $data): void
    {
        $cart = $this->createCart($data);

        $this->assertNull($this->converter->execute($cart));
    }

    /**
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function partialAddressProvider(): array
    {
        $complete = [
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'street' => ['123 Main St'],
            'city' => 'Austin',
            'country' => 'US',
            'postcode' => '78701',
        ];

        return [
            'country and postcode only' => [['country' => 'US', 'postcode' => '78701']],
            'no name' => [array_merge($complete, ['firstname' => null, 'lastname' => null])],
            'no street' => [array_merge($complete, ['street' => null])],
            'blank street line' => [array_merge($complete, ['street' => ['']])],
            'no city' => [array_merge($complete, ['city' => null])],
            'no country' => [array_merge($complete, ['country' => null])],
            'no postcode' => [array_merge($complete, ['postcode' => null])],
        ];
    }

    /**
     * @return void
     */
    public function testSingleLineStreetLeavesLineTwoNull(): void
    {
        $cart = $this->createCart([
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'street' => ['123 Main St'],
            'city' => 'Austin',
            'country' => 'US',
            'postcode' => '78701',
        ]);

        $details = $this->converter->execute($cart);

        $this->assertNotNull($details);
        $address = $details->getAddress();
        $this->assertSame('123 Main St', $address->getLineOne());
        $this->assertNull($address->getLineTwo());
        // `state` is required by the spec, so a regionless country sends it empty rather than absent.
        $this->assertSame('', $address->getState());
    }

    /**
     * @return void
     */
    public function testTwoLineStreetIsSplitAcrossBothLines(): void
    {
        $cart = $this->createCart([
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'street' => ['123 Main St', 'Apt 4'],
            'city' => 'Austin',
            'country' => 'US',
            'postcode' => '78701',
        ]);

        $details = $this->converter->execute($cart);

        $this->assertNotNull($details);
        $address = $details->getAddress();
        $this->assertSame('123 Main St', $address->getLineOne());
        $this->assertSame('Apt 4', $address->getLineTwo());
    }

    /**
     * A blank first line must not shift line two into line one's place.
     *
     * @return void
     */
    public function testBlankLeadingStreetLineIsDropped(): void
    {
        $cart = $this->createCart([
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'street' => ['', 'Apt 4'],
            'city' => 'Austin',
            'country' => 'US',
            'postcode' => '78701',
        ]);

        $details = $this->converter->execute($cart);

        $this->assertNotNull($details);
        $address = $details->getAddress();
        $this->assertSame('Apt 4', $address->getLineOne());
        $this->assertNull($address->getLineTwo());
    }

    /**
     * @return void
     */
    public function testFullyPopulatedAddressIsMapped(): void
    {
        $cart = $this->createCart([
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'street' => ['123 Main St', 'Apt 4'],
            'city' => 'Austin',
            'region' => 'Texas',
            'country' => 'US',
            'postcode' => '78701',
        ]);

        $details = $this->converter->execute($cart);

        $this->assertNotNull($details);
        $address = $details->getAddress();
        $this->assertSame([
            'name' => 'Ada Lovelace',
            'line_one' => '123 Main St',
            'city' => 'Austin',
            'state' => 'Texas',
            'country' => 'US',
            'postal_code' => '78701',
            'line_two' => 'Apt 4',
        ], $address->toArray());
    }

    /**
     * @param array<string, mixed> $addressData
     * @return Quote&MockObject
     */
    private function createCart(array $addressData): Quote
    {
        $quoteAddress = $this->getMockBuilder(QuoteAddress::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getFirstname',
                'getLastname',
                'getStreet',
                'getCity',
                'getRegion',
                'getCountry',
                'getPostcode',
                'getEmail',
                'getTelephone',
            ])
            ->getMock();

        // getStreet() returns [''] rather than [] when the quote has no street at all.
        $street = $addressData['street'] ?? [''];

        $quoteAddress->method('getFirstname')->willReturn($addressData['firstname'] ?? null);
        $quoteAddress->method('getLastname')->willReturn($addressData['lastname'] ?? null);
        $quoteAddress->method('getStreet')->willReturn($street === null ? [''] : $street);
        $quoteAddress->method('getCity')->willReturn($addressData['city'] ?? null);
        $quoteAddress->method('getRegion')->willReturn($addressData['region'] ?? null);
        $quoteAddress->method('getCountry')->willReturn($addressData['country'] ?? null);
        $quoteAddress->method('getPostcode')->willReturn($addressData['postcode'] ?? null);
        $quoteAddress->method('getEmail')->willReturn($addressData['email'] ?? null);
        $quoteAddress->method('getTelephone')->willReturn($addressData['telephone'] ?? null);

        $cart = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getShippingAddress'])
            ->addMethods(['getCustomerEmail'])
            ->getMock();
        $cart->method('getShippingAddress')->willReturn($quoteAddress);
        $cart->method('getCustomerEmail')->willReturn($addressData['customer_email'] ?? null);

        return $cart;
    }
}
