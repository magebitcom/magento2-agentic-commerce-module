<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\Data\Request;

use Magebit\AcpSpec\Api\AgenticCheckout\AddressInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInstrumentCredentialInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInstrumentInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInterfaceFactory;
use Magebit\AcpSpec\Data\AgenticCheckout\Address;
use Magebit\AcpSpec\Data\AgenticCheckout\FulfillmentDetails;
use Magebit\AcpSpec\Data\AgenticCheckout\PaymentData;
use Magebit\AcpSpec\Data\AgenticCheckout\PaymentDataInstrument;
use Magebit\AcpSpec\Data\AgenticCheckout\PaymentDataInstrumentCredential;
use Magebit\AgenticCommerce\Model\Data\Request\FulfillmentDetailsBuilder;
use Magebit\AgenticCommerce\Model\Data\Request\PaymentDataBuilder;
use PHPUnit\Framework\TestCase;

/**
 * The spec runtime never builds nested objects: it stores raw values and its typed getters return null
 * for anything that is not already the right instance. Two request fields are composite, and leaving
 * their children as arrays discarded a submitted delivery address and made completion unreachable.
 */
class NestedSpecHydrationTest extends TestCase
{
    /**
     * @return void
     */
    public function testASubmittedAddressBecomesAnAddressObject(): void
    {
        $details = $this->fulfillmentDetailsBuilder()->create(['data' => [
            'name' => 'Ada Lovelace',
            'address' => [
                'name' => 'Ada Lovelace',
                'line_one' => '1 Analytical Way',
                'city' => 'Austin',
                'state' => 'TX',
                'country' => 'US',
                'postal_code' => '78701',
            ],
        ]]);

        $address = $details->getAddress();

        $this->assertNotNull($address, 'A submitted address must not read back as absent.');
        $this->assertSame('Austin', $address->getCity());
        $this->assertSame('US', $address->getCountry());
        $this->assertSame('1 Analytical Way', $address->getLineOne());
    }

    /**
     * @return void
     */
    public function testFulfillmentDetailsWithNoAddressStayAbsent(): void
    {
        $details = $this->fulfillmentDetailsBuilder()->create(['data' => ['name' => 'Ada Lovelace']]);

        $this->assertNull($details->getAddress());
    }

    /**
     * The token sits two levels down. Unbuilt, completion refused every request with "Payment
     * credential token is required".
     *
     * @return void
     */
    public function testTheCredentialTokenSurvivesTwoLevelsOfNesting(): void
    {
        $paymentData = $this->paymentDataBuilder()->create(['data' => [
            'handler_id' => 'stripe',
            'instrument' => [
                'type' => 'card',
                'credential' => ['type' => 'shared_payment_token', 'token' => 'spt_test_123'],
            ],
        ]]);

        $this->assertSame('stripe', $paymentData->getHandlerId());
        $this->assertSame('spt_test_123', $paymentData->getInstrument()?->getCredential()->getToken());
    }

    /**
     * @return void
     */
    public function testASubmittedBillingAddressBecomesAnAddressObject(): void
    {
        $paymentData = $this->paymentDataBuilder()->create(['data' => [
            'handler_id' => 'stripe',
            'billing_address' => [
                'name' => 'Ada Lovelace',
                'line_one' => '1 Analytical Way',
                'city' => 'Austin',
                'country' => 'US',
                'postal_code' => '78701',
            ],
        ]]);

        $this->assertSame('Austin', $paymentData->getBillingAddress()?->getCity());
    }

    /**
     * Absent billing address is how a caller asks for the shipping address to be reused, so it has to
     * stay null rather than become an empty object.
     *
     * @return void
     */
    public function testPaymentDataWithNoBillingAddressStaysAbsent(): void
    {
        $paymentData = $this->paymentDataBuilder()->create(['data' => ['handler_id' => 'stripe']]);

        $this->assertNull($paymentData->getBillingAddress());
    }

    /**
     * @return FulfillmentDetailsBuilder
     */
    private function fulfillmentDetailsBuilder(): FulfillmentDetailsBuilder
    {
        return new FulfillmentDetailsBuilder(
            $this->factory(FulfillmentDetailsInterfaceFactory::class, FulfillmentDetails::class),
            $this->factory(AddressInterfaceFactory::class, Address::class)
        );
    }

    /**
     * @return PaymentDataBuilder
     */
    private function paymentDataBuilder(): PaymentDataBuilder
    {
        return new PaymentDataBuilder(
            $this->factory(PaymentDataInterfaceFactory::class, PaymentData::class),
            $this->factory(PaymentDataInstrumentInterfaceFactory::class, PaymentDataInstrument::class),
            $this->factory(
                PaymentDataInstrumentCredentialInterfaceFactory::class,
                PaymentDataInstrumentCredential::class
            ),
            $this->factory(AddressInterfaceFactory::class, Address::class)
        );
    }

    /**
     * Mirrors Magento's generated factories, which pass ['data' => $raw] to the constructor.
     *
     * @param class-string $factoryClass
     * @param class-string $dtoClass
     * @return object
     */
    private function factory(string $factoryClass, string $dtoClass): object
    {
        $factory = $this->createMock($factoryClass);
        $factory->method('create')->willReturnCallback(
            static function (array $arguments = []) use ($dtoClass): object {
                /** @var array<mixed> $data */
                $data = $arguments['data'] ?? [];

                return new $dtoClass($data);
            }
        );

        return $factory;
    }
}
