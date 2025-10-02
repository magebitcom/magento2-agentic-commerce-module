<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data;

use Magebit\AgenticCommerce\Api\Data\AddressInterface;
use Magento\Framework\DataObject;

/**
 * Address Data Transfer Object
 */
class Address extends DataObject implements AddressInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return (string) $this->getData('name');
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): AddressInterface
    {
        return $this->setData('name', $name);
    }

    /**
     * @inheritDoc
     */
    public function getLineOne(): string
    {
        return (string) $this->getData('line_one');
    }

    /**
     * @inheritDoc
     */
    public function setLineOne(string $lineOne): AddressInterface
    {
        return $this->setData('line_one', $lineOne);
    }

    /**
     * @inheritDoc
     */
    public function getLineTwo(): ?string
    {
        return $this->getData('line_two');
    }

    /**
     * @inheritDoc
     */
    public function setLineTwo(?string $lineTwo): AddressInterface
    {
        return $this->setData('line_two', $lineTwo);
    }

    /**
     * @inheritDoc
     */
    public function getCity(): string
    {
        return (string) $this->getData('city');
    }

    /**
     * @inheritDoc
     */
    public function setCity(string $city): AddressInterface
    {
        return $this->setData('city', $city);
    }

    /**
     * @inheritDoc
     */
    public function getState(): string
    {
        return (string) $this->getData('state');
    }

    /**
     * @inheritDoc
     */
    public function setState(string $state): AddressInterface
    {
        return $this->setData('state', $state);
    }

    /**
     * @inheritDoc
     */
    public function getCountry(): string
    {
        return (string) $this->getData('country');
    }

    /**
     * @inheritDoc
     */
    public function setCountry(string $country): AddressInterface
    {
        return $this->setData('country', $country);
    }

    /**
     * @inheritDoc
     */
    public function getPostalCode(): string
    {
        return (string) $this->getData('postal_code');
    }

    /**
     * @inheritDoc
     */
    public function setPostalCode(string $postalCode): AddressInterface
    {
        return $this->setData('postal_code', $postalCode);
    }
}
