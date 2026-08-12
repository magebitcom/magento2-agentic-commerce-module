<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Service;

use Magebit\AcpSpec\Api\AgenticCheckout\AddressInterface as FulfillmentAddressInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\CheckoutSessionInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\CheckoutSessionInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\LinkInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\MessageErrorInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\MessageErrorInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\MessageInfoInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\SelectedFulfillmentOptionInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\SelectedFulfillmentOptionInterfaceFactory;
use Magebit\AcpSpec\Data\AgenticCheckout\Address as SpecAddress;
use Magebit\AcpSpec\Data\AgenticCheckout\CheckoutSession;
use Magebit\AcpSpec\Data\AgenticCheckout\MessageError;
use Magebit\AgenticCommerce\Api\CartValidatorInterface;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Api\Data\Request\UpdateCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Model\Convert\CartItemToLineItem;
use Magebit\AgenticCommerce\Model\Convert\CartToBuyer;
use Magebit\AgenticCommerce\Model\Convert\CartToCapabilities;
use Magebit\AgenticCommerce\Model\Convert\CartToFulfillmentDetails;
use Magebit\AgenticCommerce\Model\Convert\CartToFulfillmentOptions;
use Magebit\AgenticCommerce\Model\Convert\CartToTotals;
use Magebit\AgenticCommerce\Model\Convert\OrderToOrderCreatedUpdatedWebhook;
use Magebit\AgenticCommerce\Model\PaymentHandlerPool;
use Magebit\AgenticCommerce\Service\CheckoutSessionService;
use Magebit\AgenticCommerce\Service\WebhookService;
use Magebit\AgenticCore\Api\OrderLinkRepositoryInterface;
use Magebit\AgenticCore\Model\Checkout\StateResolver;
use Magebit\AgenticCore\Model\Quote\AddressWriter;
use Magebit\AgenticCore\Model\Quote\RegionResolver;
use Magebit\AgenticCore\Model\Quote\LineItemOutcome;
use Magebit\AgenticCore\Model\Quote\LineItemResult;
use Magebit\AgenticCore\Model\Quote\LineItemWriter;
use Magebit\AgenticCore\Model\Quote\PersonalInformationCopier;
use Magebit\AgenticCore\Model\Quote\ShippingMethodWriter;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CurrencyInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * The five behaviour changes that come with moving this service onto the shared quote writers. Each
 * one is a defect the extraction exposed by putting this module's code next to the other's.
 */
class CheckoutSessionWritesTest extends TestCase
{
    private LineItemWriter&MockObject $lineItemWriter;

    private ShippingMethodWriter&MockObject $shippingMethodWriter;

    private OrderLinkRepositoryInterface&MockObject $orderLinkRepository;

    private CheckoutSessionService $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->lineItemWriter = $this->createMock(LineItemWriter::class);
        $this->shippingMethodWriter = $this->createMock(ShippingMethodWriter::class);
        $this->orderLinkRepository = $this->createMock(OrderLinkRepositoryInterface::class);

        $messageErrorFactory = $this->createMock(MessageErrorInterfaceFactory::class);
        $messageErrorFactory->method('create')->willReturnCallback(static fn (): MessageError => new MessageError());

        // The real resolver: it is a pure shared primitive, and stubbing it would hide exactly the
        // mapping these status tests exist to check.
        $stateResolver = new StateResolver();

        $sessionFactory = $this->createMock(CheckoutSessionInterfaceFactory::class);
        $sessionFactory->method('create')->willReturnCallback(static fn (): CheckoutSession => new CheckoutSession());

        $this->service = new CheckoutSessionService(
            $this->createMock(ConfigInterface::class),
            $this->createMock(CartRepositoryInterface::class),
            $this->createMock(GuestCartManagementInterface::class),
            $sessionFactory,
            $this->createMock(LinkInterfaceFactory::class),
            $this->createMock(GuestCartRepositoryInterface::class),
            $this->lineItemWriter,
            new AddressWriter($this->createMock(RegionResolver::class)),
            new PersonalInformationCopier(),
            $this->shippingMethodWriter,
            $this->createMock(CartItemToLineItem::class),
            $this->createMock(CartToFulfillmentDetails::class),
            $this->createMock(CartToTotals::class),
            $this->createMock(CartToFulfillmentOptions::class),
            $this->createMock(CartToCapabilities::class),
            $this->createMock(SelectedFulfillmentOptionInterfaceFactory::class),
            $this->createMock(CartValidatorInterface::class),
            $this->createMock(MessageInfoInterfaceFactory::class),
            $messageErrorFactory,
            $this->createMock(PaymentHandlerPool::class),
            $this->createMock(CartToBuyer::class),
            $this->createMock(OrderRepositoryInterface::class),
            $this->createMock(WebhookService::class),
            $this->createMock(OrderToOrderCreatedUpdatedWebhook::class),
            $this->createMock(LoggerInterface::class),
            $stateResolver,
            $this->orderLinkRepository
        );
    }

    /**
     * addDataToQuoteAddress() returned early unless the name contained a space, so a mononymous buyer
     * lost their whole delivery address — city, street, country and all.
     *
     * @return void
     */
    public function testAnAddressWithASingleWordNameIsStillApplied(): void
    {
        $cart = $this->cart();

        $this->service->addFulfillmentAddressToCart($cart, $this->specAddress(['name' => 'Ada', 'city' => 'Austin']));

        $this->assertSame('Austin', $cart->getShippingAddress()->getCity());
        $this->assertSame('1 Analytical Way', $cart->getShippingAddress()->getStreet()[0]);
        $this->assertSame('US', $cart->getShippingAddress()->getCountryId());
        $this->assertSame('Ada', $cart->getShippingAddress()->getFirstname());
        $this->assertNull($cart->getShippingAddress()->getLastname());
    }

    /**
     * addDataToQuoteAddress() wrote every field unconditionally, so an update omitting a field erased
     * the value the create call had supplied.
     *
     * @return void
     */
    public function testAnAbsentFieldDoesNotEraseAPopulatedOne(): void
    {
        $cart = $this->cart();
        $cart->getShippingAddress()->setCity('Austin');

        $this->service->addFulfillmentAddressToCart(
            $cart,
            $this->specAddress(['name' => 'Ada Lovelace', 'city' => null])
        );

        $this->assertSame('Austin', $cart->getShippingAddress()->getCity());
        $this->assertSame('Lovelace', $cart->getShippingAddress()->getLastname());
    }

    /**
     * The selected option was written to the address only. Magento reads it off the shipping assignment
     * during total collection, so the selection could be discarded.
     *
     * @return void
     */
    public function testTheSelectedOptionReachesTheShippingAssignment(): void
    {
        $cart = $this->cart();

        $selected = $this->createMock(SelectedFulfillmentOptionInterface::class);
        $selected->method('getOptionId')->willReturn('flatrate_flatrate');

        $request = $this->createMock(UpdateCheckoutSessionRequestInterface::class);
        $request->method('getLineItems')->willReturn([]);
        $request->method('getBuyer')->willReturn(null);
        $request->method('getFulfillmentDetails')->willReturn(null);
        $request->method('getSelectedFulfillmentOptions')->willReturn([$selected]);

        $this->shippingMethodWriter->expects($this->once())
            ->method('write')
            ->with($cart, 'flatrate_flatrate');

        $this->service->processSessionsRequest($cart, $request);
    }

    /**
     * addItemsToCart() let productRepository->get() throw, so one unknown SKU turned the whole request
     * into a 500 instead of a message.
     *
     * @return void
     */
    public function testAnUnknownSkuBecomesAMessageRatherThanAThrow(): void
    {
        $cart = $this->cart();
        $response = new CheckoutSession();
        $response->setId('sess_123');

        $results = [
            new LineItemResult(0, 'does-not-exist', LineItemOutcome::NotFound),
            new LineItemResult(1, '24-MB04', LineItemOutcome::Added),
        ];

        $this->service->assignCartDataToResponse($cart, $response, $results);

        $messages = array_values(array_filter(
            $response->getMessages() ?? [],
            static fn ($message): bool => $message instanceof MessageErrorInterface
        ));

        $this->assertCount(1, $messages);
        $this->assertSame(MessageErrorInterface::CODE_INVALID, $messages[0]->getCode());
        $this->assertStringContainsString('does-not-exist', (string) $messages[0]->getContent());
    }

    /**
     * The spec's MessageError.code enum is closed and has no out-of-stock value, so every unaddable
     * item reports as invalid — a distinction the other protocol can draw and this one cannot.
     *
     * @return void
     */
    public function testAnUnsalableItemAlsoReportsAsInvalid(): void
    {
        $cart = $this->cart();
        $response = new CheckoutSession();
        $response->setId('sess_123');

        $this->service->assignCartDataToResponse(
            $cart,
            $response,
            [new LineItemResult(0, 'OOS-SKU', LineItemOutcome::NotSalable)]
        );

        $messages = array_values(array_filter(
            $response->getMessages() ?? [],
            static fn ($message): bool => $message instanceof MessageErrorInterface
        ));

        $this->assertCount(1, $messages);
        $this->assertSame(MessageErrorInterface::CODE_INVALID, $messages[0]->getCode());
    }

    /**
     * The submitted items reach the shared writer, which is what reports per-item outcomes instead of
     * letting a bad SKU escape as an exception.
     *
     * @return void
     */
    public function testTheSubmittedItemsAreHandedToTheSharedWriter(): void
    {
        $cart = $this->cart();

        $this->lineItemWriter->expects($this->once())
            ->method('write')
            ->with($cart, [['sku' => '24-MB04', 'quantity' => 2]])
            ->willReturn([]);

        $this->service->addItemsToCart($cart, [$this->item('24-MB04', 2)]);
    }

    /**
     * getCartStatus() inferred a placed order from getReservedOrderId(), which Magento sets at
     * reservation rather than at placement — so a buyer who reached payment and abandoned it was told
     * their order was complete.
     *
     * @return void
     */
    public function testAReservedButUnplacedQuoteIsNotComplete(): void
    {
        $cart = $this->cart();
        $cart->setData('is_active', false);
        $cart->setData('reserved_order_id', '000000123');

        $this->orderLinkRepository->method('findOrderId')->willReturn(null);

        $this->assertSame(
            CheckoutSessionInterface::STATUS_CANCELED,
            $this->service->getCartStatus($cart, [], 'sess_abandoned')
        );
    }

    /**
     * @return void
     */
    public function testALinkedOrderIsComplete(): void
    {
        $cart = $this->cart();
        $cart->setData('is_active', false);
        $cart->setData('reserved_order_id', null);

        $this->orderLinkRepository->method('findOrderId')->willReturn(100);

        $this->assertSame(
            CheckoutSessionInterface::STATUS_COMPLETED,
            $this->service->getCartStatus($cart, [], 'sess_completed')
        );
    }

    /**
     * The same defect in the message path: a spent-but-unplaced session reported "Order placed
     * successfully" rather than a conflict.
     *
     * @return void
     */
    public function testAReservedButUnplacedQuoteDoesNotClaimAnOrderWasPlaced(): void
    {
        $cart = $this->cart();
        $cart->setData('is_active', false);
        $cart->setData('reserved_order_id', '000000123');

        $this->orderLinkRepository->method('findOrderId')->willReturn(null);

        $messages = $this->service->getCartMessages($cart, [], 'sess_abandoned');

        $this->assertCount(1, $messages);
        $this->assertInstanceOf(MessageErrorInterface::class, $messages[0]);
        $this->assertSame(MessageErrorInterface::CODE_CONFLICT, $messages[0]->getCode());
    }

    /**
     * @param array<string, mixed> $overrides
     * @return FulfillmentAddressInterface
     */
    private function specAddress(array $overrides = []): FulfillmentAddressInterface
    {
        $data = array_merge([
            'name' => 'Ada Lovelace',
            'line_one' => '1 Analytical Way',
            'line_two' => null,
            'city' => 'Austin',
            'state' => 'TX',
            'country' => 'US',
            'postal_code' => '78701',
        ], $overrides);

        $address = new SpecAddress();
        $address->setName((string) $data['name']);
        $address->setLineOne((string) $data['line_one']);
        $address->setCity((string) $data['city']);
        $address->setState((string) $data['state']);
        $address->setCountry((string) $data['country']);
        $address->setPostalCode((string) $data['postal_code']);

        if ($data['line_two'] !== null) {
            $address->setLineTwo((string) $data['line_two']);
        }

        return $address;
    }

    /**
     * A quote whose addresses are real data objects, so a write can be read back rather than merely
     * asserted on a mock.
     *
     * @return Quote&MockObject
     */
    private function cart(): Quote&MockObject
    {
        $shipping = $this->quoteAddress();
        $billing = $this->quoteAddress();

        $currency = $this->createMock(CurrencyInterface::class);
        $currency->method('getStoreCurrencyCode')->willReturn('USD');

        $cart = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getShippingAddress', 'getBillingAddress', 'getAllItems', 'getCurrency'])
            ->getMock();
        $cart->method('getShippingAddress')->willReturn($shipping);
        $cart->method('getBillingAddress')->willReturn($billing);
        $cart->method('getAllItems')->willReturn([]);
        $cart->method('getCurrency')->willReturn($currency);
        // An inactive cart reports a conflict instead of the per-item messages under test.
        $cart->setData('is_active', true);

        return $cart;
    }

    /**
     * @return QuoteAddress&MockObject
     */
    private function quoteAddress(): QuoteAddress&MockObject
    {
        return $this->getMockBuilder(QuoteAddress::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
    }

    /**
     * @param string $sku
     * @param int $quantity
     * @return \Magebit\AgenticCommerce\Api\Data\ItemInterface
     */
    private function item(string $sku, int $quantity): \Magebit\AgenticCommerce\Api\Data\ItemInterface
    {
        $item = $this->createMock(\Magebit\AgenticCommerce\Api\Data\ItemInterface::class);
        $item->method('getId')->willReturn($sku);
        $item->method('getQuantity')->willReturn($quantity);

        return $item;
    }
}
