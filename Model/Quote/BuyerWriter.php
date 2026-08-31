<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Quote;

use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magento\Quote\Model\Quote;

/**
 * Copies the buyer onto the quote. Shared by checkout sessions and carts, which both accept a buyer.
 */
class BuyerWriter
{
    /**
     * An absent field leaves what is already on the quote alone, so a later request cannot blank a
     * value by omitting it.
     *
     * @param Quote $quote
     * @param BuyerInterface $buyer
     * @return void
     */
    public function write(Quote $quote, BuyerInterface $buyer): void
    {
        if ($firstName = $buyer->getFirstName()) {
            $quote->setCustomerFirstname($firstName);
        }

        if ($lastName = $buyer->getLastName()) {
            $quote->setCustomerLastname($lastName);
        }

        if ($email = $buyer->getEmail()) {
            $quote->setCustomerEmail($email);
            $quote->getShippingAddress()->setEmail($email);
        }

        if ($phoneNumber = $buyer->getPhoneNumber()) {
            $quote->getShippingAddress()->setTelephone($phoneNumber);
        }
    }
}
