<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

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
