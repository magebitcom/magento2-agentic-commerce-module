<?php

namespace Magebit\AgenticCommerce\Model\Validation;

use Magebit\AgenticCommerce\Api\CartValidatorInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

class CustomerValidator implements CartValidatorInterface
{
    /**
     * @param CartInterface $cart
     * @return string[]
     */
    public function validate(CartInterface $cart): array
    {
        /** @var Quote $cart */

        $errors = [];

        if (!$cart->getCustomerFirstname()) {
            $errors[] = 'Customer first name is required';
        }

        if (!$cart->getCustomerLastname()) {
            $errors[] = 'Customer last name is required';
        }

        if (!$cart->getCustomerEmail()) {
            $errors[] = 'Customer email is required';
        }

        return $errors;
    }
}
