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
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInstrumentCredentialInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInstrumentCredentialInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInstrumentInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInstrumentInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInterfaceFactory;

/**
 * Builds payment data with its nested instrument, credential and billing address. Left unbuilt, the
 * credential token reads back as absent and completion refuses every request.
 */
class PaymentDataBuilder
{
    /**
     * @param PaymentDataInterfaceFactory $paymentDataFactory
     * @param PaymentDataInstrumentInterfaceFactory $instrumentFactory
     * @param PaymentDataInstrumentCredentialInterfaceFactory $credentialFactory
     * @param AddressInterfaceFactory $addressFactory
     */
    public function __construct(
        private readonly PaymentDataInterfaceFactory $paymentDataFactory,
        private readonly PaymentDataInstrumentInterfaceFactory $instrumentFactory,
        private readonly PaymentDataInstrumentCredentialInterfaceFactory $credentialFactory,
        private readonly AddressInterfaceFactory $addressFactory
    ) {
    }

    /**
     * Shaped to be passed straight to getDataInstance(), which calls it with ['data' => $raw].
     *
     * @param array<mixed> $arguments
     * @return PaymentDataInterface
     */
    public function create(array $arguments = []): PaymentDataInterface
    {
        /** @var array<string, mixed> $data */
        $data = is_array($arguments['data'] ?? null) ? $arguments['data'] : [];

        /** @var PaymentDataInterface $paymentData */
        $paymentData = $this->paymentDataFactory->create(['data' => $data]);

        $instrument = $data[PaymentDataInterface::KEY_INSTRUMENT] ?? null;

        if (is_array($instrument)) {
            $paymentData->setInstrument($this->buildInstrument($instrument));
        }

        $billingAddress = $data[PaymentDataInterface::KEY_BILLING_ADDRESS] ?? null;

        if (is_array($billingAddress)) {
            /** @var AddressInterface $addressObject */
            $addressObject = $this->addressFactory->create(['data' => $billingAddress]);
            $paymentData->setBillingAddress($addressObject);
        }

        return $paymentData;
    }

    /**
     * @param array<mixed> $data
     * @return PaymentDataInstrumentInterface
     */
    private function buildInstrument(array $data): PaymentDataInstrumentInterface
    {
        /** @var PaymentDataInstrumentInterface $instrument */
        $instrument = $this->instrumentFactory->create(['data' => $data]);

        $credential = $data[PaymentDataInstrumentInterface::KEY_CREDENTIAL] ?? null;

        if (is_array($credential)) {
            /** @var PaymentDataInstrumentCredentialInterface $credentialObject */
            $credentialObject = $this->credentialFactory->create(['data' => $credential]);
            $instrument->setCredential($credentialObject);
        }

        return $instrument;
    }
}
