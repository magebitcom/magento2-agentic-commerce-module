<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Request;

use Magebit\AcpSpec\Api\AgenticCheckout\AddressInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\AddressInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterfaceFactory;

/**
 * Builds fulfillment details with their nested address. The spec runtime stores raw values and its
 * typed getters return null for anything that is not already the right object, so a submitted address
 * left as an array reads back as absent — which silently discarded the whole delivery address.
 */
class FulfillmentDetailsBuilder
{
    /**
     * @param FulfillmentDetailsInterfaceFactory $detailsFactory
     * @param AddressInterfaceFactory $addressFactory
     */
    public function __construct(
        private readonly FulfillmentDetailsInterfaceFactory $detailsFactory,
        private readonly AddressInterfaceFactory $addressFactory
    ) {
    }

    /**
     * Shaped to be passed straight to getDataInstance(), which calls it with ['data' => $raw].
     *
     * @param array<mixed> $arguments
     * @return FulfillmentDetailsInterface
     */
    public function create(array $arguments = []): FulfillmentDetailsInterface
    {
        /** @var array<string, mixed> $data */
        $data = is_array($arguments['data'] ?? null) ? $arguments['data'] : [];

        /** @var FulfillmentDetailsInterface $details */
        $details = $this->detailsFactory->create(['data' => $data]);

        $address = $data[FulfillmentDetailsInterface::KEY_ADDRESS] ?? null;

        if (is_array($address)) {
            /** @var AddressInterface $addressObject */
            $addressObject = $this->addressFactory->create(['data' => $address]);
            $details->setAddress($addressObject);
        }

        return $details;
    }
}
