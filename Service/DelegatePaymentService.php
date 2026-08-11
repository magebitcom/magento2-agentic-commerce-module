<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Service;

use Magebit\AcpSpec\Api\DelegatePayment\DelegatePaymentResponseInterface;
use Magebit\AcpSpec\Api\DelegatePayment\DelegatePaymentResponseInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\Request\DelegatePaymentRequestInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magebit\AgenticCommerce\Api\PaymentMethodVaultHandlerInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

class DelegatePaymentService
{
    /**
     * @param DelegatePaymentResponseInterfaceFactory $delegatePaymentResponseFactory
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param PaymentMethodVaultHandlerInterface[] $paymentMethodVaultHandlers
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     * @return void
     */
    public function __construct(
        protected readonly DelegatePaymentResponseInterfaceFactory $delegatePaymentResponseFactory,
        protected readonly PaymentTokenRepositoryInterface $paymentTokenRepository,
        protected readonly ErrorResponseInterfaceFactory $errorResponseFactory,
        protected readonly array $paymentMethodVaultHandlers = []
    ) {
    }

    /**
     * @param DelegatePaymentRequestInterface $delegatePaymentRequest
     * @return DelegatePaymentResponseInterface
     */
    public function storePaymentMethod(DelegatePaymentRequestInterface $delegatePaymentRequest): DelegatePaymentResponseInterface
    {
        foreach ($this->paymentMethodVaultHandlers as $paymentMethodVaultHandler) {
            if ($paymentMethodVaultHandler->canStore($delegatePaymentRequest)) {
                return $this->delegatePaymentResponseFactory->create([ 'data' => [
                    'id' => $paymentMethodVaultHandler->handle($delegatePaymentRequest),
                    'created' => date('c'),
                    'metadata' => $delegatePaymentRequest->getMetadata(),
                ]]);
            }
        }

        throw new LocalizedException(__('Payment method cannot be stored - no valid handler found'));
    }
}
