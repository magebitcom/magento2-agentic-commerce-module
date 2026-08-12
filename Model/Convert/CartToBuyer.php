<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterfaceFactory;
use Magebit\AgenticCore\Model\Buyer\BuyerResolver;
use Magento\Quote\Model\Quote;

class CartToBuyer
{
    /**
     * @param BuyerInterfaceFactory $buyerInterfaceFactory
     * @param BuyerResolver $buyerResolver
     */
    public function __construct(
        protected readonly BuyerInterfaceFactory $buyerInterfaceFactory,
        protected readonly BuyerResolver $buyerResolver,
    ) {
    }

    /**
     * `Buyer` declares `email` required and nothing else, so a name without an email cannot be
     * reported at all, while an email on its own is already a complete buyer.
     *
     * @param Quote $cart
     * @return BuyerInterface|null
     */
    public function execute(Quote $cart): ?BuyerInterface
    {
        $identity = $this->buyerResolver->resolve($cart);

        if ($identity->email === null) {
            return null;
        }

        /** @var BuyerInterface $buyer */
        $buyer = $this->buyerInterfaceFactory->create();
        $buyer->setEmail($identity->email);

        if ($identity->firstName !== null) {
            $buyer->setFirstName($identity->firstName);
        }

        if ($identity->lastName !== null) {
            $buyer->setLastName($identity->lastName);
        }

        if ($identity->phoneNumber !== null) {
            $buyer->setPhoneNumber($identity->phoneNumber);
        }

        return $buyer;
    }
}
