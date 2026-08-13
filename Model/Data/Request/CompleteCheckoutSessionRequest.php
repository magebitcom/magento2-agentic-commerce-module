<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data\Request;

use Magebit\AgenticCommerce\Api\Data\Request\CompleteCheckoutSessionRequestInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\MarketingConsentInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\MarketingConsentInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\ValidatableDataInterface;
use Magebit\AgenticCommerce\Model\Data\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class CompleteCheckoutSessionRequest extends DataTransferObject implements
    CompleteCheckoutSessionRequestInterface,
    ValidatableDataInterface
{
    /**
     * @param PaymentDataBuilder $paymentDataBuilder
     * @param BuyerInterfaceFactory $buyerInterfaceFactory
     * @param MarketingConsentInterfaceFactory $marketingConsentFactory
     * @param array<mixed> $data
     */
    public function __construct(
        private readonly PaymentDataBuilder $paymentDataBuilder,
        private readonly BuyerInterfaceFactory $buyerInterfaceFactory,
        private readonly MarketingConsentInterfaceFactory $marketingConsentFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getBuyer(): ?BuyerInterface
    {
        return $this->getDataInstance('buyer', BuyerInterface::class, $this->buyerInterfaceFactory->create(...));
    }

    /**
     * A list rather than a single composite, so each entry is hydrated here: the spec runtime only
     * builds the top level of a field.
     *
     * @inheritDoc
     */
    public function getMarketingConsents(): array
    {
        $raw = $this->getData('marketing_consents');

        if (!is_array($raw)) {
            return [];
        }

        $consents = [];

        foreach ($raw as $entry) {
            if ($entry instanceof MarketingConsentInterface) {
                $consents[] = $entry;

                continue;
            }

            if (is_array($entry)) {
                $consents[] = $this->marketingConsentFactory->create(['data' => $entry]);
            }
        }

        return $consents;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentData(): PaymentDataInterface
    {
        $data = $this->getDataInstance(
            'payment_data',
            PaymentDataInterface::class,
            $this->paymentDataBuilder->create(...)
        );

        if (!$data instanceof PaymentDataInterface) {
            throw new \InvalidArgumentException('Payment data is required');
        }

        /** @var PaymentDataInterface $data */
        return $data;
    }

    /**
     * Validation based on spec:
     * https://developers.openai.com/commerce/specs/checkout
     *
     * @inheritDoc
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        // Validate raw data array directly per OpenAI Agentic Checkout Spec
        // allowExtraFields stays true: ACP adds optional fields between releases; ignore, never reject.
        $metadata->addGetterConstraint('rawData', new Assert\Collection([
            'fields' => [
                'buyer' => new Assert\Optional([
                    new Assert\Type('array'),
                    new Assert\Collection([
                        'fields' => [
                            'first_name' => new Assert\Required([
                                new Assert\NotBlank(),
                            ]),
                            'last_name' => new Assert\Required([
                                new Assert\NotBlank(),
                            ]),
                            'email' => new Assert\Required([
                                new Assert\NotBlank(),
                                new Assert\Email(message: 'Email must be a valid email address'),
                            ]),
                            'phone_number' => new Assert\Optional(),
                        ],
                        'allowExtraFields' => true,
                    ]),
                ]),
                'payment_data' => new Assert\Required([
                    new Assert\NotBlank(message: 'Payment data is required'),
                    new Assert\Type('array'),
                    new Assert\Collection([
                        'fields' => [
                            // The flat `token` / `provider` pair these constraints used to require is
                            // not what the spec carries, nor what the service and handler pool read:
                            // the token moved inside instrument.credential, and the handler is chosen
                            // by handler_id. Requiring the old shape made completion unreachable from
                            // either side.
                            'handler_id' => new Assert\Required([
                                new Assert\NotBlank(message: 'Payment handler_id is required'),
                                new Assert\Choice(
                                    ['stripe'],
                                    message: 'Payment handler_id must be "stripe"'
                                ),
                            ]),
                            'instrument' => new Assert\Required([
                                new Assert\NotBlank(message: 'Payment instrument is required'),
                                new Assert\Type('array'),
                                new Assert\Collection([
                                    'fields' => [
                                        'type' => new Assert\Required([
                                            new Assert\NotBlank(message: 'Instrument type is required'),
                                        ]),
                                        'credential' => new Assert\Required([
                                            new Assert\NotBlank(message: 'Instrument credential is required'),
                                            new Assert\Type('array'),
                                            new Assert\Collection([
                                                'fields' => [
                                                    'type' => new Assert\Required([
                                                        new Assert\NotBlank(
                                                            message: 'Credential type is required'
                                                        ),
                                                    ]),
                                                    'token' => new Assert\Required([
                                                        new Assert\NotBlank(
                                                            message: 'Payment token is required'
                                                        ),
                                                    ]),
                                                ],
                                                'allowExtraFields' => true,
                                            ]),
                                        ]),
                                    ],
                                    'allowExtraFields' => true,
                                ]),
                            ]),
                            'billing_address' => new Assert\Optional([
                                new Assert\Type('array'),
                                new Assert\Collection([
                                    'fields' => [
                                        'name' => new Assert\Required([
                                            new Assert\NotBlank(),
                                            new Assert\Length(max: 256),
                                        ]),
                                        'line_one' => new Assert\Required([
                                            new Assert\NotBlank(),
                                            new Assert\Length(max: 60),
                                        ]),
                                        'line_two' => new Assert\Optional([
                                            new Assert\Length(max: 60),
                                        ]),
                                        'city' => new Assert\Required([
                                            new Assert\NotBlank(),
                                            new Assert\Length(max: 60),
                                        ]),
                                        'state' => new Assert\Optional(),
                                        'country' => new Assert\Required([
                                            new Assert\NotBlank(),
                                            new Assert\Length(min: 2, max: 2),
                                            new Assert\Regex(
                                                '/^[A-Z]{2}$/',
                                                message: 'Country must be ISO-3166-1 alpha-2 (e.g., "US")'
                                            ),
                                        ]),
                                        'postal_code' => new Assert\Required([
                                            new Assert\NotBlank(),
                                            new Assert\Length(max: 20),
                                        ]),
                                    ],
                                    'allowExtraFields' => true,
                                ]),
                            ]),
                        ],
                        'allowExtraFields' => true,
                    ]),
                ]),
            ],
            'allowExtraFields' => true,
            'allowMissingFields' => false,
        ]));
    }
}
