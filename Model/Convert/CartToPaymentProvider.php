<?php

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AgenticCommerce\Api\Data\PaymentProviderInterface;
use Magebit\AgenticCommerce\Api\Data\PaymentProviderInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magebit\AgenticCommerce\Model\Convert\ConvertPrice;

class CartToPaymentProvider
{
    /**
     * @param PaymentProviderInterfaceFactory $paymentProviderFactory
     * @param ConvertPrice $convertPrice
     */
    public function __construct(
        protected readonly PaymentProviderInterfaceFactory $paymentProviderFactory,
        protected readonly ConvertPrice $convertPrice,
    ) {
    }

    /**
     * @param Quote $cart
     * @return PaymentProviderInterface
     */
    public function execute(Quote $cart): PaymentProviderInterface
    {
        /** @var PaymentProviderInterface $paymentProvider */
        $paymentProvider = $this->paymentProviderFactory->create();

        $paymentProvider->setProvider(PaymentProviderInterface::PROVIDER_STRIPE);
        $paymentProvider->setSupportedPaymentMethods([PaymentProviderInterface::PAYMENT_METHOD_CARD]);

        return $paymentProvider;
    }
}
