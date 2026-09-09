<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AcpSpec\Api\AgenticCheckout\AddressInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\AddressInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterfaceFactory;
use Magento\Quote\Model\Quote;

class CartToFulfillmentDetails
{
    /**
     * @param FulfillmentDetailsInterfaceFactory $fulfillmentDetailsInterfaceFactory
     * @param AddressInterfaceFactory $addressInterfaceFactory
     */
    public function __construct(
        protected readonly FulfillmentDetailsInterfaceFactory $fulfillmentDetailsInterfaceFactory,
        protected readonly AddressInterfaceFactory $addressInterfaceFactory,
    ) {
    }

    /**
     * Builds `fulfillment_details`, or null when the quote has no usable address yet.
     *
     * @param Quote $cart
     * @return FulfillmentDetailsInterface|null
     */
    public function execute(Quote $cart): ?FulfillmentDetailsInterface
    {
        $shippingAddress = $cart->getShippingAddress();
        $address = $this->buildAddress($cart);

        if ($address === null) {
            return null;
        }

        $name = $this->fullName($cart);
        $email = $this->toTrimmedString($shippingAddress->getEmail()) ?: $this->toTrimmedString($cart->getCustomerEmail());
        $phoneNumber = $this->toTrimmedString($shippingAddress->getTelephone());

        /** @var FulfillmentDetailsInterface $details */
        $details = $this->fulfillmentDetailsInterfaceFactory->create();
        $details->setAddress($address);

        if ($name !== '') {
            $details->setName($name);
        }

        if ($email !== '') {
            $details->setEmail($email);
        }

        if ($phoneNumber !== '') {
            $details->setPhoneNumber($phoneNumber);
        }

        return $details;
    }

    /**
     * @param Quote $cart
     * @return AddressInterface|null
     */
    private function buildAddress(Quote $cart): ?AddressInterface
    {
        $shippingAddress = $cart->getShippingAddress();

        $name = $this->fullName($cart);
        $street = $this->getStreetLines($shippingAddress->getStreet());
        $city = $this->toTrimmedString($shippingAddress->getCity());
        $country = $this->toTrimmedString($shippingAddress->getCountry());
        $postalCode = $this->toTrimmedString($shippingAddress->getPostcode());

        // The spec requires all of these, so a quote missing any of them cannot be represented.
        if ($name === '' || $street === [] || $city === '' || $country === '' || $postalCode === '') {
            return null;
        }

        /** @var AddressInterface $address */
        $address = $this->addressInterfaceFactory->create();
        $address->setName($name);
        $address->setLineOne($street[0]);
        $address->setCity($city);
        // `state` is required even where the country has no regions, so it is sent empty rather than absent.
        $address->setState($this->toTrimmedString($shippingAddress->getRegion()));
        $address->setCountry($country);
        $address->setPostalCode($postalCode);

        if (isset($street[1])) {
            $address->setLineTwo($street[1]);
        }

        return $address;
    }

    /**
     * @param Quote $cart
     * @return string
     */
    private function fullName(Quote $cart): string
    {
        $shippingAddress = $cart->getShippingAddress();

        return trim(
            $this->toTrimmedString($shippingAddress->getFirstname())
            . ' '
            . $this->toTrimmedString($shippingAddress->getLastname())
        );
    }

    /**
     * @param mixed $street
     * @return string[]
     */
    private function getStreetLines(mixed $street): array
    {
        // Quote\Address::getStreet() yields [''] for an unset street, and may hold blank trailing lines.
        $lines = array_map($this->toTrimmedString(...), is_array($street) ? $street : [$street]);

        return array_values(array_filter($lines, static fn (string $line): bool => $line !== ''));
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function toTrimmedString(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
