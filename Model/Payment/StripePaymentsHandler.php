<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Payment;

use Magebit\AgenticCommerce\Api\PaymentHandlerInterface;
use Magebit\AgenticCommerce\Api\Data\PaymentDataInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;

class StripePaymentsHandler implements PaymentHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function handle(CartInterface $cart, PaymentDataInterface $paymentData): PaymentInterface
    {
        /** @var Quote $cart */
        $payment = $cart->getPayment();

        $payment->setMethod('stripe_payments');
        $payment->setAdditionalInformation('token', $paymentData->getToken());

        return $payment;
    }
}
