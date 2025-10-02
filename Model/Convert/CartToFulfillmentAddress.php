<?php

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
     * @return AddressInterface
     */
    public function execute(Quote $cart): AddressInterface
    {
        $shippingAddress = $cart->getShippingAddress();
        $name = $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname();
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
