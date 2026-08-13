<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Service;

use LogicException;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\AddressInterface as FulfillmentAddressInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\SelectedFulfillmentOptionInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\SelectedFulfillmentOptionInterfaceFactory;

use Magebit\AgenticCommerce\Api\Data\Request\CreateCheckoutSessionRequestInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\CheckoutSessionInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\CheckoutSessionInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\ItemInterface;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\LinkInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\LinkInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\Request\CompleteCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Api\Data\Request\UpdateCheckoutSessionRequestInterface;
use Magebit\AgenticCore\Model\Quote\AddressWriter;
use Magebit\AgenticCore\Model\Quote\LineItemOutcome;
use Magebit\AgenticCore\Model\Quote\LineItemResult;
use Magebit\AgenticCore\Model\Quote\LineItemWriter;
use Magebit\AgenticCore\Model\Quote\PersonalInformationCopier;
use Magebit\AgenticCore\Model\Quote\PersonName;
use Magebit\AgenticCore\Model\Quote\PostalAddress;
use Magebit\AgenticCore\Model\Quote\ShippingMethodWriter;
use Magento\Quote\Api\CartRepositoryInterface;
use Magebit\AgenticCommerce\Model\Convert\CartItemToLineItem;
use Magebit\AgenticCommerce\Model\Convert\CartToFulfillmentDetails;
use Magebit\AgenticCommerce\Model\Convert\CartToTotals;
use Magebit\AgenticCommerce\Model\Convert\CartToFulfillmentOptions;
use Magebit\AgenticCommerce\Model\Convert\CartToCapabilities;
use Magebit\AgenticCommerce\Api\CartValidatorInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\MessageErrorInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\MessageErrorInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\MessageInfoInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\MessageInfoInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\Webhook\WebhookEventInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magebit\AgenticCommerce\Model\PaymentHandlerPool;
use Magebit\AgenticCommerce\Model\Convert\CartToBuyer;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magebit\AgenticCommerce\Service\WebhookService;
use Magebit\AgenticCommerce\Model\Convert\OrderToAcpOrder;
use Magebit\AgenticCommerce\Model\Convert\OrderToOrderCreatedUpdatedWebhook;
use Magebit\AgenticCore\Api\OrderLinkRepositoryInterface;
use Magebit\AgenticCore\Model\Checkout\CheckoutState;
use Magebit\AgenticCore\Model\Checkout\StateResolver;
use Psr\Log\LoggerInterface;

/**
 * TODO: Split this service into smaller pieces
 */
class CheckoutSessionService
{
    /**
     * @param ConfigInterface $config
     * @param CartRepositoryInterface $cartRepository
     * @param GuestCartManagementInterface $guestCartManagement
     * @param CheckoutSessionInterfaceFactory $checkoutSessionResponseFactory
     * @param LinkInterfaceFactory $linkInterfaceFactory
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param LineItemWriter $lineItemWriter
     * @param AddressWriter $addressWriter
     * @param PersonalInformationCopier $personalInformationCopier
     * @param ShippingMethodWriter $shippingMethodWriter
     * @param CartItemToLineItem $cartItemToLineItem
     * @param CartToFulfillmentDetails $cartToFulfillmentDetails
     * @param CartToTotals $cartToTotals
     * @param CartToFulfillmentOptions $cartToFulfillmentOptions
     * @param CartToCapabilities $cartToCapabilities
     * @param SelectedFulfillmentOptionInterfaceFactory $selectedFulfillmentOptionFactory
     * @param CartValidatorInterface $cartValidator
     * @param MessageInfoInterfaceFactory $messageInfoFactory
     * @param MessageErrorInterfaceFactory $messageErrorFactory
     * @param PaymentHandlerPool $paymentHandlerPool
     * @param CartToBuyer $cartToBuyer
     * @param OrderRepositoryInterface $orderRepository
     * @param WebhookService $webhookService
     * @param OrderToOrderCreatedUpdatedWebhook $orderToOrderCreatedUpdatedWebhook
     * @param LoggerInterface $logger
     * @param StateResolver $stateResolver
     * @param OrderLinkRepositoryInterface $orderLinkRepository
     * @param OrderToAcpOrder $orderToAcpOrder
     */
    public function __construct(
        protected readonly ConfigInterface $config,
        protected readonly CartRepositoryInterface $cartRepository,
        protected readonly GuestCartManagementInterface $guestCartManagement,
        protected readonly CheckoutSessionInterfaceFactory $checkoutSessionResponseFactory,
        protected readonly LinkInterfaceFactory $linkInterfaceFactory,
        protected readonly GuestCartRepositoryInterface $guestCartRepository,
        protected readonly LineItemWriter $lineItemWriter,
        protected readonly AddressWriter $addressWriter,
        protected readonly PersonalInformationCopier $personalInformationCopier,
        protected readonly ShippingMethodWriter $shippingMethodWriter,
        protected readonly CartItemToLineItem $cartItemToLineItem,
        protected readonly CartToFulfillmentDetails $cartToFulfillmentDetails,
        protected readonly CartToTotals $cartToTotals,
        protected readonly CartToFulfillmentOptions $cartToFulfillmentOptions,
        protected readonly CartToCapabilities $cartToCapabilities,
        protected readonly SelectedFulfillmentOptionInterfaceFactory $selectedFulfillmentOptionFactory,
        protected readonly CartValidatorInterface $cartValidator,
        protected readonly MessageInfoInterfaceFactory $messageInfoFactory,
        protected readonly MessageErrorInterfaceFactory $messageErrorFactory,
        protected readonly PaymentHandlerPool $paymentHandlerPool,
        protected readonly CartToBuyer $cartToBuyer,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly WebhookService $webhookService,
        protected readonly OrderToOrderCreatedUpdatedWebhook $orderToOrderCreatedUpdatedWebhook,
        protected readonly LoggerInterface $logger,
        protected readonly StateResolver $stateResolver,
        protected readonly OrderLinkRepositoryInterface $orderLinkRepository,
        protected readonly OrderToAcpOrder $orderToAcpOrder,
    ) {
    }

    /**
     * @param CreateCheckoutSessionRequestInterface $checkoutSessionsRequest
     * @return CheckoutSessionInterface
     */
    public function create(CreateCheckoutSessionRequestInterface $checkoutSessionsRequest): CheckoutSessionInterface
    {
        $maskedCartId = $this->guestCartManagement->createEmptyCart();
        $cart = $this->guestCartRepository->get($maskedCartId);

        /** @var CheckoutSessionInterface $response */
        $response = $this->checkoutSessionResponseFactory->create();
        $response->setId($maskedCartId);

        $lineItemResults = $this->processSessionsRequest($cart, $checkoutSessionsRequest);
        $this->cartRepository->save($cart);
        $this->assignCartDataToResponse($cart, $response, $lineItemResults);

        $this->logger->info('Checkout session created', ['cart_id' => $maskedCartId]);

        return $response;
    }

    /**
     * @param string $sessionId
     * @param UpdateCheckoutSessionRequestInterface $checkoutSessionsRequest
     * @return CheckoutSessionInterface
     */
    public function update(string $sessionId, UpdateCheckoutSessionRequestInterface $checkoutSessionsRequest): CheckoutSessionInterface
    {
        /** @var Quote $cart */
        $cart = $this->guestCartRepository->get($sessionId);

        $lineItemResults = $this->processSessionsRequest($cart, $checkoutSessionsRequest);
        $cart->collectTotals();
        $this->cartRepository->save($cart);

        $response = $this->checkoutSessionResponseFactory->create();
        $response->setId($sessionId);
        $this->assignCartDataToResponse($cart, $response, $lineItemResults);

        $this->logger->info('Checkout session updated', ['cart_id' => $sessionId]);

        return $response;
    }

    /**
     * @param string $sessionId
     * @param CompleteCheckoutSessionRequestInterface $checkoutSessionsRequest
     * @return CheckoutSessionInterface
     */
    public function complete(string $sessionId, CompleteCheckoutSessionRequestInterface $checkoutSessionsRequest): CheckoutSessionInterface
    {
        $cart = $this->guestCartRepository->get($sessionId);

        if (!$cart->getIsActive()) {
            throw new LocalizedException(__('Cart is not active. Please create a new checkout session'));
        }

        $paymentData = $checkoutSessionsRequest->getPaymentData();

        // The token moved inside the instrument's credential; without one there is nothing to charge.
        if (($paymentData->getInstrument()?->getCredential()?->getToken() ?? '') === '') {
            throw new LocalizedException(__('Payment credential token is required'));
        }

        if ($checkoutSessionsRequest->getBuyer()) {
            $this->addBuyerToCart($cart, $checkoutSessionsRequest->getBuyer());
        }

        $this->setCartEmailAddress($cart);

        $billingAddress = $paymentData->getBillingAddress();

        if ($billingAddress !== null) {
            $this->addBillingAddressToCart($cart, $billingAddress);
        } else {
            $this->copyShippingAddressToBillingAddress($cart);
        }

        $cartPayment = $this->paymentHandlerPool->get($cart, $paymentData);
        $this->cartRepository->save($cart);

        $orderId = $this->guestCartManagement->placeOrder($sessionId, $cartPayment);

        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);
        $quoteId = $order->getQuoteId();
        $this->orderLinkRepository->link(
            ComplianceService::IDEMPOTENCY_SCOPE,
            $sessionId,
            is_numeric($quoteId) ? (int) $quoteId : null,
            (int) $orderId
        );

        $this->webhookService->dispatch(
            $this->orderToOrderCreatedUpdatedWebhook->execute(
                $order,
                WebhookEventInterface::TYPE_ORDER_CREATED,
                $sessionId
            ),
            $sessionId
        );

        /** @var CheckoutSessionInterface $response */
        $response = $this->checkoutSessionResponseFactory->create();
        $response->setId($sessionId);
        $this->assignCartDataToResponse($cart, $response);
        $response->setStatus(CheckoutSessionInterface::STATUS_COMPLETED);
        // Required on completion: the spec returns CheckoutSessionWithOrder here, and without it an
        // agent gets no machine-readable reference to the order it just created.
        $response->setOrder($this->orderToAcpOrder->convert($order, $sessionId));
        $message = $this->infoMessage(sprintf('Order placed successfully: %s', $order->getIncrementId()));

        $response->setMessages([$message]);

        $this->logger->info('Checkout session completed', ['cart_id' => $sessionId, 'order_id' => $order->getIncrementId()]);

        return $response;
    }

    /**
     * @param string $sessionId
     * @return CheckoutSessionInterface
     */
    public function retrieve(string $sessionId): CheckoutSessionInterface
    {
        $cart = $this->guestCartRepository->get($sessionId);
        /** @var Quote $cart */
        $cart->collectTotals();

        $response = $this->checkoutSessionResponseFactory->create();
        $response->setId($sessionId);
        $this->assignCartDataToResponse($cart, $response);
        return $response;
    }

    /**
     * @param string $sessionId
     * @return CheckoutSessionInterface
     */
    public function cancel(string $sessionId): CheckoutSessionInterface
    {
        $cart = $this->guestCartRepository->get($sessionId);

        if (!$cart->getIsActive()) {
            if ($cart->getReservedOrderId() !== null) {
                throw new LocalizedException(__('Order is already placed. Please contact support to cancel the order'));
            }

            throw new LocalizedException(__('Cart is already canceled'));
        }

        /** @var Quote $cart */
        $cart->setIsActive(false);
        $this->cartRepository->save($cart);

        $this->logger->info('Checkout session canceled', ['cart_id' => $sessionId]);

        return $this->retrieve($sessionId);
    }

    /**
     * @param CartInterface $cart
     * @param CreateCheckoutSessionRequestInterface|UpdateCheckoutSessionRequestInterface $checkoutSessionsRequest
     * @return LineItemResult[] One per submitted item, for the response to report
     */
    public function processSessionsRequest(
        CartInterface $cart,
        CreateCheckoutSessionRequestInterface|UpdateCheckoutSessionRequestInterface $checkoutSessionsRequest
    ): array {
        /** @var Quote $cart */
        $lineItemResults = [];

        if ($checkoutSessionsRequest->getLineItems()) {
            $lineItemResults = $this->addItemsToCart($cart, $checkoutSessionsRequest->getLineItems());
        }

        if ($checkoutSessionsRequest->getBuyer()) {
            $this->addBuyerToCart($cart, $checkoutSessionsRequest->getBuyer());
        }

        $fulfillmentDetails = $checkoutSessionsRequest->getFulfillmentDetails();

        if ($fulfillmentDetails?->getAddress() !== null) {
            $this->addFulfillmentAddressToCart($cart, $fulfillmentDetails->getAddress());
        }

        if ($checkoutSessionsRequest instanceof UpdateCheckoutSessionRequestInterface) {
            // Magento carries one shipping method per address, so the first selection is the one applied.
            $selected = $checkoutSessionsRequest->getSelectedFulfillmentOptions()[0] ?? null;

            if ($selected !== null) {
                $this->shippingMethodWriter->write($cart, $selected->getOptionId());
            }
        }

        return $lineItemResults;
    }

    /**
     * @param CartInterface $cart
     * @param CheckoutSessionInterface $response
     * @param LineItemResult[] $lineItemResults Outcomes for items submitted on this request, if any
     * @return void
     */
    public function assignCartDataToResponse(
        CartInterface $cart,
        CheckoutSessionInterface $response,
        array $lineItemResults = []
    ): void {
        /** @var Quote $cart */
        $lineItems = [];

        foreach ($cart->getAllItems() as $item) {
            $lineItem = $this->cartItemToLineItem->execute($item);
            $lineItems[] = $lineItem;
        }

        $fulfillmentDetails = $this->cartToFulfillmentDetails->execute($cart);
        $totals = $this->cartToTotals->execute($cart);
        $fulfillmentOptions = $this->cartToFulfillmentOptions->execute($cart);
        $capabilities = $this->cartToCapabilities->execute($cart);
        $buyer = $this->cartToBuyer->execute($cart);
        $links = $this->getLinks();
        $validationErrors = $this->cartValidator->validate($cart);

        // Being very optimistic here
        /** @var string $currency */
        $currency = $cart->getCurrency()?->getStoreCurrencyCode();

        $response->setLineItems($lineItems);

        if ($fulfillmentDetails) {
            $response->setFulfillmentDetails($fulfillmentDetails);
        }

        $response->setTotals($totals);
        $response->setFulfillmentOptions($fulfillmentOptions);
        $response->setCapabilities($capabilities);
        $response->setCurrency($currency);

        if ($buyer) {
            $response->setBuyer($buyer);
        }

        $response->setLinks($links);
        $response->setMessages(array_merge(
            $this->getCartMessages($cart, $validationErrors, (string) $response->getId()),
            $this->lineItemMessages($lineItemResults)
        ));
        $response->setStatus($this->getCartStatus($cart, $validationErrors, (string) $response->getId()));

        $response->setSelectedFulfillmentOptions($this->getSelectedFulfillmentOptions($cart));
    }

    /**
     * @param CartInterface $cart
     * @param ItemInterface[] $items
     * @return LineItemResult[]
     */
    public function addItemsToCart(CartInterface $cart, array $items): array
    {
        /** @var Quote $cart */
        return $this->lineItemWriter->write($cart, array_map(
            fn (ItemInterface $item): array => [
                'sku' => (string) $item->getId(),
                'quantity' => $item->getQuantity(),
            ],
            array_values($items)
        ));
    }

    /**
     * Every unaddable item reports as invalid: the spec's MessageError.code enum is closed and has no
     * out-of-stock value, so the distinction the other protocol can draw is not available here.
     *
     * @param LineItemResult[] $results
     * @return MessageErrorInterface[]
     */
    private function lineItemMessages(array $results): array
    {
        $messages = [];

        foreach ($results as $result) {
            $content = match ($result->outcome) {
                LineItemOutcome::Added => null,
                LineItemOutcome::NotFound => sprintf('Product "%s" does not exist.', $result->sku),
                LineItemOutcome::NotSalable => sprintf(
                    'Product "%s" is not available for purchase.',
                    $result->sku
                ),
                LineItemOutcome::Rejected => $result->reason
                    ?? sprintf('Product "%s" could not be added.', $result->sku),
            };

            if ($content !== null) {
                $messages[] = $this->errorMessage(MessageErrorInterface::CODE_INVALID, $content);
            }
        }

        return $messages;
    }

    /**
     * @param CartInterface $cart
     * @param string[] $errors
     * @param string $sessionId Masked cart id the caller issued, not the quote's numeric id
     * @return string
     */
    public function getCartStatus(CartInterface $cart, array $errors, string $sessionId): string
    {
        $state = $this->stateResolver->resolve($cart, $this->hasOrder($sessionId), $errors !== []);

        return match ($state) {
            CheckoutState::Completed => CheckoutSessionInterface::STATUS_COMPLETED,
            CheckoutState::Canceled => CheckoutSessionInterface::STATUS_CANCELED,
            CheckoutState::Ready => CheckoutSessionInterface::STATUS_READY_FOR_PAYMENT,
            CheckoutState::Incomplete => CheckoutSessionInterface::STATUS_NOT_READY_FOR_PAYMENT,
        };
    }

    /**
     * @param CartInterface $cart
     * @param string[] $errors
     * @param string $sessionId Masked cart id the caller issued, not the quote's numeric id
     * @return array<MessageInfoInterface|MessageErrorInterface>
     */
    public function getCartMessages(CartInterface $cart, array $errors, string $sessionId): array
    {
        if (!$cart->getIsActive()) {
            $orderId = $this->orderLinkRepository->findOrderId(
                ComplianceService::IDEMPOTENCY_SCOPE,
                $sessionId
            );

            if ($orderId !== null) {
                return [
                    $this->infoMessage(
                        sprintf('Order placed successfully: %s', $this->incrementIdOf($orderId))
                    ),
                ];
            }

            // `cart_not_active` is not one of the spec's codes; a spent session is a conflict.
            return [
                $this->errorMessage(
                    MessageErrorInterface::CODE_CONFLICT,
                    'Cart is not active. Please create a new checkout session'
                ),
            ];
        }

        return array_map(
            fn (string $error): MessageErrorInterface => $this->errorMessage(
                MessageErrorInterface::CODE_INVALID,
                $error
            ),
            $errors
        );
    }

    /**
     * A placed order is now a recorded fact rather than an inference from the reserved increment id,
     * which Magento sets at reservation and not at placement.
     *
     * @param string $sessionId
     * @return bool
     */
    private function hasOrder(string $sessionId): bool
    {
        return $this->orderLinkRepository->findOrderId(
            ComplianceService::IDEMPOTENCY_SCOPE,
            $sessionId
        ) !== null;
    }

    /**
     * @param int $orderId
     * @return string
     */
    private function incrementIdOf(int $orderId): string
    {
        try {
            return (string) $this->orderRepository->get($orderId)->getIncrementId();
        } catch (NoSuchEntityException $exception) {
            // The link outlives nothing — the order cascades on delete — but a mid-flight read can
            // still miss it, and reporting the internal id beats failing the whole response.
            return (string) $orderId;
        }
    }

    /**
     * @param string $content
     * @return MessageInfoInterface
     */
    protected function infoMessage(string $content): MessageInfoInterface
    {
        /** @var MessageInfoInterface $message */
        $message = $this->messageInfoFactory->create();
        $message->setType(MessageInfoInterface::TYPE_INFO);
        $message->setContentType(MessageInfoInterface::CONTENT_TYPE_PLAIN);
        $message->setContent($content);

        return $message;
    }

    /**
     * @param string $code One of the spec's MessageError codes
     * @param string $content
     * @return MessageErrorInterface
     */
    protected function errorMessage(string $code, string $content): MessageErrorInterface
    {
        /** @var MessageErrorInterface $message */
        $message = $this->messageErrorFactory->create();
        $message->setType(MessageErrorInterface::TYPE_ERROR);
        $message->setCode($code);
        $message->setContentType(MessageErrorInterface::CONTENT_TYPE_PLAIN);
        $message->setContent($content);

        return $message;
    }

    /**
     * @return LinkInterface[]
     */
    public function getLinks(): array
    {
        $linksConfig = $this->config->getCheckoutSessionLinks();

        return array_map(function (array $link) : LinkInterface {
            $linkData = [
                'type' => $link['type'],
                'url' => $link['link']
            ];

            return $this->linkInterfaceFactory->create(['data' => $linkData]);
        }, $linksConfig);
    }

    /**
     * @param CartInterface $cart
     * @param BuyerInterface $buyer
     * @return void
     */
    public function addBuyerToCart(CartInterface $cart, BuyerInterface $buyer): void
    {
        /** @var Quote $cart */
        if ($firstName = $buyer->getFirstName()) {
            $cart->setCustomerFirstname($firstName);
        }

        if ($lastName = $buyer->getLastName()) {
            $cart->setCustomerLastname($lastName);
        }

        if ($email = $buyer->getEmail()) {
            $cart->setCustomerEmail($email);
        }

        if ($email = $buyer->getEmail()) {
            $cart->getShippingAddress()->setEmail($email);
        }

        if ($phoneNumber = $buyer->getPhoneNumber()) {
            $cart->getShippingAddress()->setTelephone($phoneNumber);
        }
    }

    /**
     * @param CartInterface $cart
     * @param FulfillmentAddressInterface $address
     * @return void
     */
    public function addFulfillmentAddressToCart(CartInterface $cart, FulfillmentAddressInterface $address): void
    {
        /** @var Quote $cart */
        $this->addressWriter->write($cart->getShippingAddress(), $this->toPostalAddress($address));

        if (!$cart->getCustomerFirstname() || !$cart->getCustomerLastname()) {
            [$firstName, $lastName] = PersonName::split($address->getName());

            if ($firstName === null) {
                return;
            }

            $cart->setCustomerFirstname($firstName);
            $cart->setCustomerLastname($lastName);
        }
    }

    /**
     * @param CartInterface $cart
     * @param FulfillmentAddressInterface $address
     * @return void
     */
    public function addBillingAddressToCart(CartInterface $cart, FulfillmentAddressInterface $address): void
    {
        /** @var Quote $cart */
        $billingAddress = $cart->getBillingAddress();
        $this->addressWriter->write($billingAddress, $this->toPostalAddress($address));

        if ($billingAddress && !$billingAddress->getTelephone() && $cart->getShippingAddress()) {
            $billingAddress->setTelephone($cart->getShippingAddress()->getTelephone());
        }
    }

    /**
     * Magento selects one shipping method for the whole cart, so this is a single-element list
     * covering every item until fulfillment groups are implemented.
     *
     * @param Quote $cart
     * @return SelectedFulfillmentOptionInterface[]
     */
    protected function getSelectedFulfillmentOptions(Quote $cart): array
    {
        $shippingMethod = $cart->getShippingAddress()->getShippingMethod();

        if (!$shippingMethod) {
            return [];
        }

        $itemIds = [];

        foreach ($cart->getAllItems() as $item) {
            $itemIds[] = (string)$item->getId();
        }

        /** @var SelectedFulfillmentOptionInterface $selected */
        $selected = $this->selectedFulfillmentOptionFactory->create();
        $selected->setType(SelectedFulfillmentOptionInterface::TYPE_SHIPPING);
        $selected->setOptionId($shippingMethod);
        $selected->setItemIds($itemIds);

        return [$selected];
    }

    /**
     * @param CartInterface $cart
     * @return void
     */
    protected function setCartEmailAddress(CartInterface $cart): void
    {
        /** @var Quote $cart */
        $shippingAddress = $cart->getShippingAddress();

        if (!$shippingAddress->getEmail() && $cart->getCustomerEmail()) {
            $shippingAddress->setEmail($cart->getCustomerEmail());
        }
    }

    /**
     * Turns the spec address into the shared writer's protocol-free value object.
     *
     * @param FulfillmentAddressInterface $address
     * @return PostalAddress
     */
    protected function toPostalAddress(FulfillmentAddressInterface $address): PostalAddress
    {
        [$firstName, $lastName] = PersonName::split($address->getName());

        return new PostalAddress(
            streetLine: $address->getLineOne(),
            extendedLine: $address->getLineTwo(),
            locality: $address->getCity(),
            region: $address->getState(),
            country: $address->getCountry(),
            postalCode: $address->getPostalCode(),
            firstName: $firstName,
            lastName: $lastName
        );
    }

    /**
     * @param CartInterface $cart
     * @return void
     */
    protected function copyShippingAddressToBillingAddress(CartInterface $cart): void
    {
        /** @var Quote $cart */
        $shippingAddress = $cart->getShippingAddress();
        $billingAddress = $cart->getBillingAddress();

        $this->personalInformationCopier->copyIdentity($shippingAddress, $billingAddress);
        $this->personalInformationCopier->copyPostalFields($shippingAddress, $billingAddress);
    }
}
