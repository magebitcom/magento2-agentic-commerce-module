<?php

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
