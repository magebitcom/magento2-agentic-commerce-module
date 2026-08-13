<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Api\Data\Request;

use Magebit\AcpSpec\Api\AgenticCheckout\CapabilitiesInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscountsRequestInterface;
use Magebit\AgenticCommerce\Api\Data\ValidatableDataInterface;

interface CreateCheckoutSessionRequestInterface extends ValidatableDataInterface, RequestInterface
{
    /**
     * Items to add to the session. Each carries a `quantity` the spec's `Item` does not declare;
     * see the `Item.quantity` defect recorded in the acp-php-spec README.
     *
     * @return \Magebit\AgenticCommerce\Api\Data\ItemInterface[]
     */
    public function getLineItems(): array;

    /**
     * @return string ISO 4217 currency code
     */
    public function getCurrency(): string;

    /**
     * @return \Magebit\AcpSpec\Api\AgenticCheckout\CapabilitiesInterface|null
     */
    public function getCapabilities(): ?CapabilitiesInterface;

    /**
     * @return \Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterface|null
     */
    public function getFulfillmentDetails(): ?FulfillmentDetailsInterface;

    /**
     * @return \Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface|null
     */
    public function getBuyer(): ?BuyerInterface;

    /**
     * Discount codes the agent submitted. An empty array clears what was applied.
     *
     * @return DiscountsRequestInterface|null
     */
    public function getDiscounts(): ?DiscountsRequestInterface;
}
