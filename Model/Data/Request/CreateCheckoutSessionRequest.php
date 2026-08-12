<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data\Request;

use Magebit\AcpSpec\Api\AgenticCheckout\CapabilitiesInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\CapabilitiesInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterface;
use Magebit\AgenticCommerce\Api\Data\ItemInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CreateCheckoutSessionRequestInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AgenticCommerce\Api\Data\ItemInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\ValidatableDataInterface;
use Magebit\AgenticCommerce\Model\Data\DataTransferObject;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCheckoutSessionRequest extends DataTransferObject implements
    CreateCheckoutSessionRequestInterface,
    ValidatableDataInterface
{
    /**
     * @param ItemInterfaceFactory $itemInterfaceFactory
     * @param FulfillmentDetailsBuilder $fulfillmentDetailsBuilder
     * @param CapabilitiesInterfaceFactory $capabilitiesInterfaceFactory
     * @param BuyerInterfaceFactory $buyerInterfaceFactory
     * @param array<mixed> $data
     */
    public function __construct(
        private readonly ItemInterfaceFactory $itemInterfaceFactory,
        private readonly FulfillmentDetailsBuilder $fulfillmentDetailsBuilder,
        private readonly CapabilitiesInterfaceFactory $capabilitiesInterfaceFactory,
        private readonly BuyerInterfaceFactory $buyerInterfaceFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getLineItems(): array
    {
        return $this->getDataInstanceArray(
            'line_items',
            ItemInterface::class,
            $this->itemInterfaceFactory->create(...)
        );
    }

    /**
     * @inheritDoc
     */
    public function getCurrency(): string
    {
        return $this->getDataStringOrNull('currency') ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getCapabilities(): ?CapabilitiesInterface
    {
        return $this->getDataInstance(
            'capabilities',
            CapabilitiesInterface::class,
            $this->capabilitiesInterfaceFactory->create(...)
        );
    }

    /**
     * @inheritDoc
     */
    public function getFulfillmentDetails(): ?FulfillmentDetailsInterface
    {
        return $this->getDataInstance(
            'fulfillment_details',
            FulfillmentDetailsInterface::class,
            $this->fulfillmentDetailsBuilder->create(...)
        );
    }

    /**
     * @inheritDoc
     */
    public function getBuyer(): ?BuyerInterface
    {
        return $this->getDataInstance('buyer', BuyerInterface::class, $this->buyerInterfaceFactory->create(...));
    }

    /**
     * Constraints on the `address` inside `fulfillment_details`. Shared with the update request, and
     * a method rather than a constant because Symfony constraints are objects.
     *
     * @return array<string, Assert\Required|Assert\Optional>
     */
    public static function addressFields(): array
    {
        return [
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
                new Assert\Regex('/^[A-Z]{2}$/', message: 'Country must be ISO-3166-1 alpha-2 (e.g., "US")'),
            ]),
            'postal_code' => new Assert\Required([
                new Assert\NotBlank(),
                new Assert\Length(max: 20),
            ]),
        ];
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
                'line_items' => new Assert\Required([
                    new Assert\NotBlank(message: 'Line items are required'),
                    new Assert\Type('array'),
                    new Assert\Count(min: 1, minMessage: 'At least one line item is required'),
                    new Assert\All([
                        new Assert\Collection([
                            'fields' => [
                                'id' => new Assert\Required([
                                    new Assert\NotBlank(message: 'Line item id is required'),
                                ]),
                                // Accepted despite the spec's Item being additionalProperties:false
                                // without it — every upstream example sends it. See step 38.
                                'quantity' => new Assert\Required([
                                    new Assert\NotBlank(message: 'Line item quantity is required'),
                                    new Assert\Type('int'),
                                    new Assert\GreaterThan(0, message: 'Quantity must be greater than 0'),
                                ]),
                            ],
                            'allowExtraFields' => true,
                        ]),
                    ]),
                ]),
                'currency' => new Assert\Required([
                    new Assert\NotBlank(message: 'Currency is required'),
                    new Assert\Regex('/^[A-Z]{3}$/', message: 'Currency must be an ISO 4217 code (e.g., "USD")'),
                ]),
                'capabilities' => new Assert\Required([
                    new Assert\Type('array'),
                ]),
                'fulfillment_details' => new Assert\Optional([
                    new Assert\Type('array'),
                    new Assert\Collection([
                        'fields' => [
                            'name' => new Assert\Optional([
                                new Assert\Length(max: 256),
                            ]),
                            'phone_number' => new Assert\Optional(),
                            'email' => new Assert\Optional([
                                new Assert\Email(message: 'Email must be a valid email address'),
                            ]),
                            'address' => new Assert\Optional([
                                new Assert\Type('array'),
                                new Assert\Collection([
                                    'fields' => self::addressFields(),
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
