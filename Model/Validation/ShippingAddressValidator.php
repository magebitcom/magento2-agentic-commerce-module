<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Validation;

use Magebit\AgenticCommerce\Api\CartValidatorInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

class ShippingAddressValidator implements CartValidatorInterface
{
    /**
     * @param CartInterface $cart
     * @return string[]
     */
    public function validate(CartInterface $cart): array
    {
        /** @var Quote $cart */
        $shippingAddress = $cart->getShippingAddress();
        $errors = [];

        $validateErrors = $shippingAddress->validate();

        if (is_array($validateErrors)) {
            $errors = array_merge($errors, $validateErrors);
        }

        if (!$shippingAddress->getTelephone()) {
            $errors[] = 'Buyer phone number is required';
        }

        if (!$shippingAddress->getShippingMethod()) {
            $errors[] = 'Shipping method is required';
        }

        return $errors;
    }
}
