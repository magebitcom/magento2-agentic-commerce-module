<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Payment;

use Magebit\AgenticCommerce\Api\PaymentHandlerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInterface;
use Magento\Framework\Exception\LocalizedException;
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
        $payment->setAdditionalInformation('token', $this->getCredentialToken($paymentData));

        return $payment;
    }

    /**
     * The token sits two levels down now: the instrument names its type, the credential carries the
     * value. A delegated payment without one cannot be charged.
     *
     * @param PaymentDataInterface $paymentData
     * @return string
     * @throws LocalizedException If the request carried no credential token
     */
    private function getCredentialToken(PaymentDataInterface $paymentData): string
    {
        $token = $paymentData->getInstrument()?->getCredential()?->getToken();

        if ($token === null || $token === '') {
            throw new LocalizedException(__('The payment data carries no credential token.'));
        }

        return $token;
    }
}
