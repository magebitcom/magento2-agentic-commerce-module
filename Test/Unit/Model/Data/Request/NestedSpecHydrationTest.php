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

use Magebit\AcpSpec\Api\AgenticCheckout\AddressInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInstrumentCredentialInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInstrumentInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInterface;
use Magebit\AcpSpec\Data\AgenticCheckout\Address;
use Magebit\AcpSpec\Data\AgenticCheckout\PaymentData;
use Magebit\AcpSpec\Data\AgenticCheckout\PaymentDataInstrument;
use Magebit\AcpSpec\Data\AgenticCheckout\PaymentDataInstrumentCredential;
use Magebit\AgenticCommerce\Api\Data\Request\CompleteCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Model\Data\Request\CompleteCheckoutSessionRequest;
use Magebit\AgenticCore\Model\Request\Hydrator;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeProcessor;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * The spec runtime never builds nested objects: it stores raw values and its typed getters return null
 * for anything that is not already the right instance. Building them is the shared hydrator's job, and
 * a request whose children stay as arrays loses a submitted address and cannot be completed.
 */
class NestedSpecHydrationTest extends TestCase
{
    /**
     * Which class stands in for each interface the hydrator asks for.
     */
    private const IMPLEMENTATIONS = [
        AddressInterface::class => Address::class,
        PaymentDataInterface::class => PaymentData::class,
        PaymentDataInstrumentInterface::class => PaymentDataInstrument::class,
        PaymentDataInstrumentCredentialInterface::class => PaymentDataInstrumentCredential::class,
    ];

    /**
     * The token sits two levels down. Unbuilt, completion refused every request with "Payment
     * credential token is required".
     *
     * @return void
     */
    public function testTheCredentialTokenSurvivesTwoLevelsOfNesting(): void
    {
        $request = $this->hydrate([
            'payment_data' => [
                'handler_id' => 'stripe',
                'instrument' => [
                    'type' => 'card',
                    'credential' => ['type' => 'shared_payment_token', 'token' => 'spt_test_123'],
                ],
            ],
        ]);

        $paymentData = $request->getPaymentData();

        $this->assertSame('stripe', $paymentData->getHandlerId());
        $this->assertSame('spt_test_123', $paymentData->getInstrument()?->getCredential()->getToken());
    }

    /**
     * @return void
     */
    public function testASubmittedBillingAddressBecomesAnAddressObject(): void
    {
        $request = $this->hydrate([
            'payment_data' => [
                'handler_id' => 'stripe',
                'billing_address' => [
                    'name' => 'Ada Lovelace',
                    'line_one' => '1 Analytical Way',
                    'city' => 'Austin',
                    'country' => 'US',
                    'postal_code' => '78701',
                ],
            ],
        ]);

        $this->assertSame('Austin', $request->getPaymentData()->getBillingAddress()?->getCity());
    }

    /**
     * Absent billing address is how a caller asks for the shipping address to be reused, so it has to
     * stay null rather than become an empty object.
     *
     * @return void
     */
    public function testPaymentDataWithNoBillingAddressStaysAbsent(): void
    {
        $request = $this->hydrate(['payment_data' => ['handler_id' => 'stripe']]);

        $this->assertNull($request->getPaymentData()->getBillingAddress());
    }

    /**
     * @param array<string, mixed> $body
     * @return CompleteCheckoutSessionRequestInterface
     */
    private function hydrate(array $body): CompleteCheckoutSessionRequestInterface
    {
        $request = new CompleteCheckoutSessionRequest();

        $this->hydrator()->populateWithArray(
            $request,
            $body,
            CompleteCheckoutSessionRequestInterface::class
        );

        return $request;
    }

    /**
     * @return Hydrator
     */
    private function hydrator(): Hydrator
    {
        $objectFactory = $this->createMock(ObjectFactory::class);
        $objectFactory->method('create')->willReturnCallback(
            static function (string $type): object {
                $class = self::IMPLEMENTATIONS[$type] ?? $type;

                return new $class();
            }
        );

        $methodsMap = $this->createMock(MethodsMap::class);
        $methodsMap->method('getMethodReturnType')->willReturnCallback(
            static fn (string $type, string $method): string => self::returnTypeOf($type, $method)
        );

        return new Hydrator($objectFactory, new TypeProcessor(), $methodsMap);
    }

    /**
     * Stands in for Magento's own annotation reader, which needs a cache this test has no use for.
     *
     * @param string $type Interface the getter is declared on
     * @param string $method Getter name
     * @return string The type the getter returns
     */
    private static function returnTypeOf(string $type, string $method): string
    {
        $returnType = (new ReflectionMethod($type, $method))->getReturnType();

        return $returnType instanceof ReflectionNamedType ? $returnType->getName() : 'mixed';
    }
}
