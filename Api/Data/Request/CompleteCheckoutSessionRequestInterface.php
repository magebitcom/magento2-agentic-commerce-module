<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

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
