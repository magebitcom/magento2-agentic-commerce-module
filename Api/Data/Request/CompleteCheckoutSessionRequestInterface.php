<?php

namespace Magebit\AgenticCommerce\Api\Data\Request;

use Magebit\AgenticCommerce\Api\Data\BuyerInterface;
use Magebit\AgenticCommerce\Api\Data\PaymentDataInterface;

interface CompleteCheckoutSessionRequestInterface
{
    /**
     * @return \Magebit\AgenticCommerce\Api\Data\BuyerInterface|null
     */
    public function getBuyer(): ?BuyerInterface;

    /**
     * @return \Magebit\AgenticCommerce\Api\Data\PaymentDataInterface
     */
    public function getPaymentData(): PaymentDataInterface;
}
