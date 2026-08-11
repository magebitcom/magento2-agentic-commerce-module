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
     * Builds the optional ACP fulfillment_address, or null when the quote has no usable address yet.
     *
     * @param Quote $cart
     * @return AddressInterface|null
     */
    public function execute(Quote $cart): ?AddressInterface
    {
        $shippingAddress = $cart->getShippingAddress();

        $name = trim(
            $this->toTrimmedString($shippingAddress->getFirstname())
            . ' '
            . $this->toTrimmedString($shippingAddress->getLastname())
        );
        $street = $this->getStreetLines($shippingAddress->getStreet());
        $city = $this->toTrimmedString($shippingAddress->getCity());
        $country = $this->toTrimmedString($shippingAddress->getCountry());
        $postalCode = $this->toTrimmedString($shippingAddress->getPostcode());

        // Every one of these is non-nullable on AddressInterface, so a quote missing any cannot be represented.
        if ($name === '' || $street === [] || $city === '' || $country === '' || $postalCode === '') {
            return null;
        }

        // State stays optional: a shipping-estimate quote may carry country and postcode but no region.
        $state = $this->toTrimmedString($shippingAddress->getRegion());

        /** @var AddressInterface $address */
        $address = $this->addressInterfaceFactory->create();
        $address->setName($name);
        $address->setLineOne($street[0]);
        $address->setLineTwo($street[1] ?? null);
        $address->setCity($city);
        $address->setState($state !== '' ? $state : null);
        $address->setCountry($country);
        $address->setPostalCode($postalCode);

        return $address;
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
