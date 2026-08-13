<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Api\Data\Request;

use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscountsRequestInterface;
use Magebit\AgenticCommerce\Api\Data\ValidatableDataInterface;

interface UpdateCheckoutSessionRequestInterface extends RequestInterface, ValidatableDataInterface
{
    /**
     * @return \Magebit\AgenticCommerce\Api\Data\ItemInterface[]
     */
    public function getLineItems(): array;

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

    /**
     * A selection per fulfillment group, replacing the single option id earlier revisions carried.
     *
     * @return \Magebit\AcpSpec\Api\AgenticCheckout\SelectedFulfillmentOptionInterface[]
     */
    public function getSelectedFulfillmentOptions(): array;
}
