<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Service;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CreateCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Api\Data\Response\CheckoutSessionResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\CheckoutSessionResponseInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\ItemInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magebit\AgenticCommerce\Api\Data\BuyerInterface;
use Magebit\AgenticCommerce\Api\Data\LinkInterface;
use Magebit\AgenticCommerce\Api\Data\LinkInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\Request\CompleteCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Api\Data\Request\UpdateCheckoutSessionRequestInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Api\CartRepositoryInterface;
use Magebit\AgenticCommerce\Model\Convert\CartItemToLineItem;
use Magebit\AgenticCommerce\Model\Convert\CartToFulfillmentAddress;
use Magebit\AgenticCommerce\Model\Convert\CartToTotals;
use Magebit\AgenticCommerce\Model\Convert\CartToFulfillmentOptions;
use Magebit\AgenticCommerce\Model\Convert\CartToPaymentProvider;
use Magebit\AgenticCommerce\Api\CartValidatorInterface;
use Magebit\AgenticCommerce\Api\Data\MessageInterface;
use Magebit\AgenticCommerce\Api\Data\MessageInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magebit\AgenticCommerce\Model\PaymentHandlerPool;
use Magebit\AgenticCommerce\Model\Convert\CartToBuyer;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * TODO: Split this service into smaller pieces
 */
class CheckoutSessionService
{
    /**
     * @param ConfigInterface $config
     * @param CartRepositoryInterface $cartRepository
     * @param GuestCartManagementInterface $guestCartManagement
     * @param CheckoutSessionResponseInterfaceFactory $checkoutSessionResponseFactory
     * @param LinkInterfaceFactory $linkInterfaceFactory
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CartItemToLineItem $cartItemToLineItem
     * @param CartToFulfillmentAddress $cartToFulfillmentAddress
     * @param CartToTotals $cartToTotals
     * @param CartToFulfillmentOptions $cartToFulfillmentOptions
     * @param CartToPaymentProvider $cartToPaymentProvider
     * @param CartValidatorInterface $cartValidator
     * @param MessageInterfaceFactory $messageInterfaceFactory
     * @param PaymentHandlerPool $paymentHandlerPool
     */
    public function __construct(
        protected readonly ConfigInterface $config,
        protected readonly CartRepositoryInterface $cartRepository,
        protected readonly GuestCartManagementInterface $guestCartManagement,
        protected readonly CheckoutSessionResponseInterfaceFactory $checkoutSessionResponseFactory,
        protected readonly LinkInterfaceFactory $linkInterfaceFactory,
        protected readonly GuestCartRepositoryInterface $guestCartRepository,
        protected readonly ProductRepositoryInterface $productRepository,
        protected readonly CartItemToLineItem $cartItemToLineItem,
        protected readonly CartToFulfillmentAddress $cartToFulfillmentAddress,
        protected readonly CartToTotals $cartToTotals,
        protected readonly CartToFulfillmentOptions $cartToFulfillmentOptions,
        protected readonly CartToPaymentProvider $cartToPaymentProvider,
        protected readonly CartValidatorInterface $cartValidator,
        protected readonly MessageInterfaceFactory $messageInterfaceFactory,
        protected readonly PaymentHandlerPool $paymentHandlerPool,
        protected readonly CartToBuyer $cartToBuyer,
        protected readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    /**
     * @param CreateCheckoutSessionRequestInterface $checkoutSessionsRequest
     * @return CheckoutSessionResponseInterface
     */
    public function create(CreateCheckoutSessionRequestInterface $checkoutSessionsRequest): CheckoutSessionResponseInterface
    {
        $maskedCartId = $this->guestCartManagement->createEmptyCart();
        $cart = $this->guestCartRepository->get($maskedCartId);

        /** @var CheckoutSessionResponseInterface $response */
        $response = $this->checkoutSessionResponseFactory->create();
        $response->setId($maskedCartId);

        $this->processSessionsRequest($cart, $checkoutSessionsRequest);
        $this->cartRepository->save($cart);
        $this->assignCartDataToResponse($cart, $response);

        return $response;
    }

    /**
     * @param string $sessionId
     * @param UpdateCheckoutSessionRequestInterface $checkoutSessionsRequest
     * @return CheckoutSessionResponseInterface
     */
    public function update(string $sessionId, UpdateCheckoutSessionRequestInterface $checkoutSessionsRequest): CheckoutSessionResponseInterface
    {
        /** @var Quote $cart */
        $cart = $this->guestCartRepository->get($sessionId);

        $this->processSessionsRequest($cart, $checkoutSessionsRequest);
        $cart->collectTotals();
        $this->cartRepository->save($cart);

        $response = $this->checkoutSessionResponseFactory->create();
        $response->setId($sessionId);
        $this->assignCartDataToResponse($cart, $response);
        return $response;
    }

    /**
     * @param string $sessionId
     * @param CompleteCheckoutSessionRequestInterface $checkoutSessionsRequest
     * @return CheckoutSessionResponseInterface
     */
    public function complete(string $sessionId, CompleteCheckoutSessionRequestInterface $checkoutSessionsRequest): CheckoutSessionResponseInterface
    {
        $cart = $this->guestCartRepository->get($sessionId);
        $paymentToken = $checkoutSessionsRequest->getPaymentData()->getToken();

        if (!$paymentToken) {
            throw new LocalizedException(__('Payment token is required'));
        }

        if ($checkoutSessionsRequest->getBuyer()) {
            $this->addBuyerToCart($cart, $checkoutSessionsRequest->getBuyer());
        }

        $this->setCartEmailAddress($cart);

        if ($checkoutSessionsRequest->getPaymentData()->getBillingAddress()) {
            $this->addBillingAddressToCart($cart, $checkoutSessionsRequest->getPaymentData()->getBillingAddress());
        } else {
            $this->copyShippingAddressToBillingAddress($cart);
        }

        $cartPayment = $this->paymentHandlerPool->get($cart, $checkoutSessionsRequest->getPaymentData());
        $this->cartRepository->save($cart);

        $orderId = $this->guestCartManagement->placeOrder($sessionId, $cartPayment);

        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);

        /** @var CheckoutSessionResponseInterface $response */
        $response = $this->checkoutSessionResponseFactory->create();
        $response->setId($sessionId);
        $this->assignCartDataToResponse($cart, $response);
        $response->setStatus(CheckoutSessionResponseInterface::STATUS_COMPLETED);
        $message = $this->messageInterfaceFactory->create(['data' => [
            'type' => MessageInterface::TYPE_INFO,
            'code' => 'order_placed',
            'content_type' => MessageInterface::CONTENT_TYPE_PLAIN,
            'content' => sprintf('Order placed successfully: %s', $order->getIncrementId()),
        ]]);

        $response->setMessages([$message]);

        return $response;
    }

    /**
     * @param string $sessionId
     * @return CheckoutSessionResponseInterface
     */
    public function retrieve(string $sessionId): CheckoutSessionResponseInterface
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
     * @param CartInterface $cart
     * @param CreateCheckoutSessionRequestInterface|UpdateCheckoutSessionRequestInterface $checkoutSessionsRequest
     * @return void
     */
    public function processSessionsRequest(
        CartInterface $cart,
        CreateCheckoutSessionRequestInterface|UpdateCheckoutSessionRequestInterface $checkoutSessionsRequest
    ): void {
        /** @var Quote $cart */
        if ($checkoutSessionsRequest->getItems()) {
            $this->addItemsToCart($cart, $checkoutSessionsRequest->getItems());
        }

        if ($checkoutSessionsRequest->getBuyer()) {
            $this->addBuyerToCart($cart, $checkoutSessionsRequest->getBuyer());
        }

        if ($checkoutSessionsRequest->getFulfillmentAddress()) {
            $this->addFulfillmentAddressToCart($cart, $checkoutSessionsRequest->getFulfillmentAddress());
        }

        if ($checkoutSessionsRequest instanceof UpdateCheckoutSessionRequestInterface) {
            if ($checkoutSessionsRequest->getFulfillmentOptionId()) {
                $cart->getShippingAddress()->setShippingMethod($checkoutSessionsRequest->getFulfillmentOptionId());
            }
        }
    }

    /**
     * @param CartInterface $cart
     * @param CheckoutSessionResponseInterface $response
     * @return void
     */
    public function assignCartDataToResponse(CartInterface $cart, CheckoutSessionResponseInterface $response): void
    {
        /** @var Quote $cart */
        $lineItems = [];

        foreach ($cart->getAllItems() as $item) {
            $lineItem = $this->cartItemToLineItem->execute($item);
            $lineItems[] = $lineItem;
        }

        $fulfillmentAddress = $this->cartToFulfillmentAddress->execute($cart);
        $totals = $this->cartToTotals->execute($cart);
        $fulfillmentOptions = $this->cartToFulfillmentOptions->execute($cart);
        $paymentProvider = $this->cartToPaymentProvider->execute($cart);
        $buyer = $this->cartToBuyer->execute($cart);
        $links = $this->getLinks();
        $validationErrors = $this->cartValidator->validate($cart);

        // Being very optimistic here
        /** @var string $currency */
        $currency = $cart->getCurrency()?->getStoreCurrencyCode();

        $response->setLineItems($lineItems);
        $response->setFulfillmentAddress($fulfillmentAddress);
        $response->setTotals($totals);
        $response->setFulfillmentOptions($fulfillmentOptions);
        $response->setPaymentProvider($paymentProvider);
        $response->setCurrency($currency);

        if ($buyer) {
            $response->setBuyer($buyer);
        }

        $response->setLinks($links);
        $response->setMessages($this->getCartMessages($validationErrors));
        $response->setStatus($this->getCartStatus($validationErrors));

        $shippingMethod = $cart->getShippingAddress()->getShippingMethod();

        if ($shippingMethod) {
            $response->setFulfillmentOptionId($shippingMethod);
        }
    }

    /**
     * @param CartInterface $cart
     * @param ItemInterface[] $items
     * @return void
     */
    public function addItemsToCart(CartInterface $cart, array $items): void
    {
        /** @var Quote $cart */
        $cart->removeAllItems();

        foreach ($items as $item) {
            /** @var Product $product */
            $product = $this->getProduct($item);

            /** @var Quote $cart */
            $cart->addProduct($product, $item->getQuantity());
        }
    }

    /**
     * @param string[] $errors
     * @return string
     */
    public function getCartStatus(array $errors): string
    {
        $status = CheckoutSessionResponseInterface::STATUS_NOT_READY_FOR_PAYMENT;

        if (empty($errors)) {
            $status = CheckoutSessionResponseInterface::STATUS_READY_FOR_PAYMENT;
        }

        return $status;
    }

    /**
     * @param string[] $errors
     * @return MessageInterface[]
     */
    public function getCartMessages(array $errors): array
    {
        return array_map(function ($error) {
            return $this->messageInterfaceFactory->create(['data' => [
                'type' => MessageInterface::TYPE_ERROR,
                'code' => 'invalid',
                'content_type' => MessageInterface::CONTENT_TYPE_PLAIN,
                'content' => $error,
            ]]);
        }, $errors);
    }

    /**
     * @return LinkInterface[]
     */
    public function getLinks(): array
    {
        $linksConfig = $this->config->getCheckoutSessionLinks();

        return array_map(function ($link) {
            return $this->linkInterfaceFactory->create(['data' => $link]);
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
            $cart->getShippingAddress()->setTelephone((string) $buyer->getPhoneNumber());
        }
    }

    /**
     * @param ItemInterface $item
     * @return ProductInterface
     */
    public function getProduct(ItemInterface $item): ProductInterface
    {
        return $this->productRepository->get($item->getId());
    }

    /**
     * @param CartInterface $cart
     * @param AddressInterface $address
     * @return void
     */
    public function addFulfillmentAddressToCart(CartInterface $cart, AddressInterface $address): void
    {
        /** @var Quote $cart */
        $shippingAddress = $cart->getShippingAddress();
        $this->addDataToQuoteAddress($shippingAddress, $address);

        if (!$cart->getCustomerFirstname() || !$cart->getCustomerLastname()) {
            [$firstName, $lastName] = explode(' ', $address->getName(), 2);
            $cart->setCustomerFirstname($firstName);
            $cart->setCustomerLastname($lastName);
        }
    }

    /**
     * @param CartInterface $cart
     * @param AddressInterface $address
     * @return void
     */
    public function addBillingAddressToCart(CartInterface $cart, AddressInterface $address): void
    {
        /** @var Quote $cart */
        $billingAddress = $cart->getBillingAddress();
        $this->addDataToQuoteAddress($billingAddress, $address);
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
     * @param QuoteAddressInterface $cartAddress
     * @param AddressInterface $address
     * @return void
     */
    protected function addDataToQuoteAddress(QuoteAddressInterface $cartAddress, AddressInterface $address): void
    {
        [$firstName, $lastName] = explode(' ', $address->getName(), 2);

        $street = array_filter([$address->getLineOne(), $address->getLineTwo()]);

        $cartAddress->setFirstname($firstName);
        $cartAddress->setLastname($lastName);
        $cartAddress->setStreet($street);
        $cartAddress->setCity($address->getCity());
        $cartAddress->setRegion($address->getState());
        $cartAddress->setCountryId($address->getCountry());
        $cartAddress->setPostcode($address->getPostalCode());
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

        $billingAddress->setFirstname($shippingAddress->getFirstname());
        $billingAddress->setLastname($shippingAddress->getLastname());
        $billingAddress->setStreet($shippingAddress->getStreet());
        $billingAddress->setCity($shippingAddress->getCity());
        $billingAddress->setRegionId($shippingAddress->getRegionId());
        $billingAddress->setCountryId($shippingAddress->getCountryId());
        $billingAddress->setPostcode($shippingAddress->getPostcode());
        $billingAddress->setTelephone($shippingAddress->getTelephone());
        $billingAddress->setEmail($shippingAddress->getEmail());
    }
}
