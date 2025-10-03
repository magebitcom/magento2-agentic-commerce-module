<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Api;

use Magebit\AgenticCommerce\Api\Data\PaymentDataInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;

interface PaymentHandlerInterface
{
    /**
     * @param CartInterface $cart
     * @param PaymentDataInterface $paymentData
     * @return PaymentInterface
     */
    public function handle(CartInterface $cart, PaymentDataInterface $paymentData): PaymentInterface;
}
