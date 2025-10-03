<?php

namespace Magebit\AgenticCommerce\Api;

use Magento\Quote\Api\Data\CartInterface;

interface CartValidatorInterface
{
    /**
     * Validate cart. Return true if cart is valid, false otherwise.
     *
     * @param CartInterface $cart
     * @return string[]
     */
    public function validate(CartInterface $cart): array;
}
