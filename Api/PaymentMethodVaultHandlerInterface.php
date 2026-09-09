<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Api;

use Magebit\AcpSpec\Api\DelegatePayment\DelegatePaymentRequestInterface;

interface PaymentMethodVaultHandlerInterface
{
    /**
     * Stores incoming payment method and returns a vault token
     * @param DelegatePaymentRequestInterface $delegatePaymentRequest
     * @return string
     */
    public function handle(DelegatePaymentRequestInterface $delegatePaymentRequest): string;

    /**
     * Checks if the payment method can be stored
     * @param DelegatePaymentRequestInterface $delegatePaymentRequest
     * @return bool
     */
    public function canStore(DelegatePaymentRequestInterface $delegatePaymentRequest): bool;
}
