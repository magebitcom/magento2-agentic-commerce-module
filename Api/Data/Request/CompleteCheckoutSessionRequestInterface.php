<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Api\Data\Request;

use Magebit\AcpSpec\Api\AgenticCheckout\MarketingConsentInterface;

use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInterface;
use Magebit\AgenticCommerce\Api\Data\ValidatableDataInterface;

interface CompleteCheckoutSessionRequestInterface extends ValidatableDataInterface, RequestInterface
{
    /**
     * @return \Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface|null
     */
    public function getBuyer(): ?BuyerInterface;

    /**
     * @return \Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInterface
     */
    public function getPaymentData(): PaymentDataInterface;

    /**
     * The buyer's marketing consent decisions, one per channel.
     *
     * @return MarketingConsentInterface[]
     */
    public function getMarketingConsents(): array;
}
