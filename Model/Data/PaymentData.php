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
use Magebit\AgenticCommerce\Api\Data\PaymentDataInterface;
use Magebit\AgenticCommerce\Model\Data\DataTransferObject;
use Magebit\AgenticCommerce\Api\Data\AddressInterfaceFactory;

/**
 * Payment Data Data Transfer Object
 */
class PaymentData extends DataTransferObject implements PaymentDataInterface
{
    public function __construct(
        private readonly AddressInterfaceFactory $addressInterfaceFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getToken(): string
    {
        return (string) $this->getData('token');
    }

    /**
     * @inheritDoc
     */
    public function setToken(string $token): PaymentDataInterface
    {
        return $this->setData('token', $token);
    }

    /**
     * @inheritDoc
     */
    public function getProvider(): string
    {
        return (string) $this->getData('provider');
    }

    /**
     * @inheritDoc
     */
    public function setProvider(string $provider): PaymentDataInterface
    {
        return $this->setData('provider', $provider);
    }

    /**
     * @inheritDoc
     */
    public function getBillingAddress(): ?AddressInterface
    {
        $address = $this->getData('billing_address');
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
    public function setBillingAddress(?AddressInterface $address): PaymentDataInterface
    {
        return $this->setData('billing_address', $address);
    }
}
