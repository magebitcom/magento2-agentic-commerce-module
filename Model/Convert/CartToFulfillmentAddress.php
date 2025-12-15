<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AgenticCommerce\Api\Data\AddressInterface;
use Magebit\AgenticCommerce\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Model\Quote;

class CartToFulfillmentAddress
{
    /**
     * @param AddressInterfaceFactory $addressInterfaceFactory
     */
    public function __construct(
        protected readonly AddressInterfaceFactory $addressInterfaceFactory,
    ) {
    }

    /**
     * @param Quote $cart
     * @return AddressInterface|null
     */
    public function execute(Quote $cart): ?AddressInterface
    {
        $shippingAddress = $cart->getShippingAddress();

        // Check if required address fields are present
        if (!$shippingAddress->getCity()
            || !$shippingAddress->getCountry()
            || !$shippingAddress->getPostcode()
            || !$shippingAddress->getStreet()
        ) {
            return null;
        }

        $name = trim($shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname());

        // If name is empty, use a placeholder or return null
        if (empty($name)) {
            return null;
        }

        /** @var AddressInterface $address */
        $address = $this->addressInterfaceFactory->create();
        $address->setName($name);
        $address->setLineOne($shippingAddress->getStreet()[0]);
        $address->setLineTwo($shippingAddress->getStreet()[1] ?? null);
        $address->setCity($shippingAddress->getCity());
        $address->setState($shippingAddress->getRegion());
        $address->setCountry($shippingAddress->getCountry());
        $address->setPostalCode($shippingAddress->getPostcode());

        return $address;
    }
}
