<?php

namespace Magebit\AgenticCommerce\Service;

use Magebit\AgenticCommerce\Api\Data\AddressInterface;
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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Api\CartRepositoryInterface;
use Magebit\AgenticCommerce\Model\Convert\CartItemToLineItem;
use Magebit\AgenticCommerce\Model\Convert\CartToFulfillmentAddress;
use Magebit\AgenticCommerce\Model\Convert\CartToTotals;
use Magebit\AgenticCommerce\Model\Convert\CartToFulfillmentOptions;
use Magebit\AgenticCommerce\Model\Convert\CartToPaymentProvider;

class CheckoutSessionService
{
    public function __construct(
        protected readonly CartRepositoryInterface $cartRepository,
        protected readonly GuestCartManagementInterface $guestCartManagement,
        protected readonly CheckoutSessionResponseInterfaceFactory $checkoutSessionResponseFactory,
        protected readonly GuestCartRepositoryInterface $guestCartRepository,
        protected readonly ProductRepositoryInterface $productRepository,
        protected readonly CartItemToLineItem $cartItemToLineItem,
        protected readonly CartToFulfillmentAddress $cartToFulfillmentAddress,
        protected readonly CartToTotals $cartToTotals,
        protected readonly CartToFulfillmentOptions $cartToFulfillmentOptions,
        protected readonly CartToPaymentProvider $cartToPaymentProvider,
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

        $this->addItemsToCart($cart, $checkoutSessionsRequest->getItems());

        if ($checkoutSessionsRequest->getBuyer()) {
            $this->addBuyerToCart($cart, $checkoutSessionsRequest->getBuyer());
        }

        if ($checkoutSessionsRequest->getFulfillmentAddress()) {
            $this->addFulfillmentAddressToCart($cart, $checkoutSessionsRequest->getFulfillmentAddress());
        }

        $this->cartRepository->save($cart);
        $this->assignCartDataToResponse($cart, $response);

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

        $response->setLineItems($lineItems);
        $response->setFulfillmentAddress($fulfillmentAddress);
        $response->setTotals($totals);
        $response->setFulfillmentOptions($fulfillmentOptions);
        $response->setPaymentProvider($paymentProvider);

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
        foreach ($items as $item) {
            /** @var Product $product */
            $product = $this->getProduct($item);

            /** @var Quote $cart */
            $cart->addProduct($product, $item->getQuantity());
        }
    }

    /**
     * @param CartInterface $cart
     * @param BuyerInterface $buyer
     * @return void
     */
    public function addBuyerToCart(CartInterface $cart, BuyerInterface $buyer): void
    {
        /** @var Quote $cart */
        $cart->setCustomerFirstname($buyer->getFirstName());
        $cart->setCustomerLastname($buyer->getLastName());
        $cart->setCustomerEmail($buyer->getEmail());
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
        [$firstName, $lastName] = explode(' ', $address->getName(), 2);

        $street = array_filter([$address->getLineOne(), $address->getLineTwo()]);

        $shippingAddress->setFirstname($firstName);
        $shippingAddress->setLastname($lastName);
        $shippingAddress->setStreet($street);
        $shippingAddress->setCity($address->getCity());
        $shippingAddress->setRegion($address->getState());
        $shippingAddress->setCountryId($address->getCountry());
        $shippingAddress->setPostcode($address->getPostalCode());
    }
}
