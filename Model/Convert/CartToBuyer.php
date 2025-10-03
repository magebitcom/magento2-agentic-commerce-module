<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AgenticCommerce\Api\Data\BuyerInterface;
use Magebit\AgenticCommerce\Api\Data\BuyerInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magebit\AgenticCommerce\Model\Convert\ConvertPrice;

class CartToBuyer
{
    /**
     * @param BuyerInterfaceFactory $buyerInterfaceFactory
     * @param ConvertPrice $convertPrice
     */
    public function __construct(
        protected readonly BuyerInterfaceFactory $buyerInterfaceFactory,
        protected readonly ConvertPrice $convertPrice,
    ) {
    }

    /**
     * @param Quote $cart
     * @return BuyerInterface|null
     */
    public function execute(Quote $cart): ?BuyerInterface
    {
        /** @var BuyerInterface $buyer */
        $buyer = $this->buyerInterfaceFactory->create();

        $firstName = $this->getBuyerFirstName($cart);

        if (!$firstName) {
            return null;
        }

        $buyer->setFirstName($firstName);

        $lastName = $this->getBuyerLastName($cart);

        if (!$lastName) {
            return null;
        }

        $buyer->setLastName($lastName);

        $email = $cart->getCustomerEmail();

        if (!$email) {
            return null;
        }

        $buyer->setEmail($email);

        $buyer->setPhoneNumber($cart->getShippingAddress()->getTelephone());

        return $buyer;
    }

    /**
     * @param Quote $cart
     * @return string|null
     */
    public function getBuyerFirstName(Quote $cart): ?string
    {
        if ($cart->getCustomerFirstname()) {
            return $cart->getCustomerFirstname();
        }

        $shippingAddress = $cart->getShippingAddress();

        if ($shippingAddress->getFirstname()) {
            return $shippingAddress->getFirstname();
        }

        return null;
    }

    /**
     * @param Quote $cart
     * @return string|null
     */
    public function getBuyerLastName(Quote $cart): ?string
    {
        if ($cart->getCustomerLastname()) {
            return $cart->getCustomerLastname();
        }

        $shippingAddress = $cart->getShippingAddress();

        if ($shippingAddress->getLastname()) {
            return $shippingAddress->getLastname();
        }

        return null;
    }
}
