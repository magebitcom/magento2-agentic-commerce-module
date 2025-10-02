<?php

namespace Magebit\AgenticCommerce\Api\Data\Request;

use Magebit\AgenticCommerce\Api\Data\AddressInterface;
use Magebit\AgenticCommerce\Api\Data\BuyerInterface;

interface CreateCheckoutSessionRequestInterface
{
    /**
     * Get items
     *
     * @return \Magebit\AgenticCommerce\Api\Data\ItemInterface[]
     */
    public function getItems(): array;

    /**
     * @return \Magebit\AgenticCommerce\Api\Data\AddressInterface|null
     */
    public function getFulfillmentAddress(): ?AddressInterface;

    /**
     * @return \Magebit\AgenticCommerce\Api\Data\BuyerInterface|null
     */
    public function getBuyer(): ?BuyerInterface;
}
