<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data\Request;

use Magebit\AgenticCommerce\Api\Data\ItemInterface;
use Magebit\AgenticCommerce\Api\Data\Request\UpdateCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Api\Data\AddressInterface;
use Magebit\AgenticCommerce\Api\Data\BuyerInterface;
use Magebit\AgenticCommerce\Api\Data\ItemInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\AddressInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\BuyerInterfaceFactory;
use Magento\Framework\DataObject;

class UpdateCheckoutSessionRequest extends DataObject implements UpdateCheckoutSessionRequestInterface
{
    /**
     * @param ItemInterfaceFactory $itemInterfaceFactory
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param BuyerInterfaceFactory $buyerInterfaceFactory
     * @param array<mixed> $data
     */
    public function __construct(
        private readonly ItemInterfaceFactory $itemInterfaceFactory,
        private readonly AddressInterfaceFactory $addressInterfaceFactory,
        private readonly BuyerInterfaceFactory $buyerInterfaceFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getItems(): array
    {
        /** @var array<mixed> $items */
        $items = $this->getData('items') ?? [];

        return array_map(function ($item) {
            if ($item instanceof ItemInterface) {
                return $item;
            }

            /** @var array<mixed> $item */
            return $this->itemInterfaceFactory->create(['data' => $item]);
        }, $items);
    }

    /**
     * @inheritDoc
     */
    public function getFulfillmentAddress(): ?AddressInterface
    {
        $address = $this->getData('fulfillment_address');

        if ($address instanceof AddressInterface) {
            return $address;
        }
        if (is_array($address)) {
            return $this->addressInterfaceFactory->create(['data' => $address]);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getBuyer(): ?BuyerInterface
    {
        $buyer = $this->getData('buyer');

        if ($buyer instanceof BuyerInterface) {
            return $buyer;
        }
        if (is_array($buyer)) {
            return $this->buyerInterfaceFactory->create(['data' => $buyer]);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getFulfillmentOptionId(): ?string
    {
        // @phpstan-ignore return.type
        return $this->getData('fulfillment_option_id');
    }
}
