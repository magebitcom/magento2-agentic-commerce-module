<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data\Request;

use Magebit\AgenticCommerce\Api\Data\ItemInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CreateCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Api\Data\AddressInterface;
use Magebit\AgenticCommerce\Api\Data\BuyerInterface;
use Magebit\AgenticCommerce\Api\Data\ItemInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\AddressInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\BuyerInterfaceFactory;
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
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param BuyerInterfaceFactory $buyerInterfaceFactory
     * @param array<mixed> $data
     */
    public function __construct(
        private readonly ItemInterfaceFactory $itemInterfaceFactory,
        private readonly AddressInterfaceFactory $addressInterfaceFactory,
        private readonly BuyerInterfaceFactory $buyerInterfaceFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getItems(): array
    {
        return $this->getDataInstanceArray('items', ItemInterface::class, $this->itemInterfaceFactory->create(...));
    }

    /**
     * @inheritDoc
     */
    public function getFulfillmentAddress(): ?AddressInterface
    {
        return $this->getDataInstance(
            'fulfillment_address',
            AddressInterface::class,
            $this->addressInterfaceFactory->create(...)
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
                'items' => new Assert\Required([
                    new Assert\NotBlank(message: 'Items are required'),
                    new Assert\Type('array'),
                    new Assert\Count(min: 1, minMessage: 'At least one item is required'),
                    new Assert\All([
                        new Assert\Collection([
                            'fields' => [
                                'id' => new Assert\Required([
                                    new Assert\NotBlank(message: 'Item id is required'),
                                ]),
                                'quantity' => new Assert\Required([
                                    new Assert\NotBlank(message: 'Item quantity is required'),
                                    new Assert\Type('int'),
                                    new Assert\GreaterThan(0, message: 'Quantity must be greater than 0'),
                                ]),
                            ],
                            'allowExtraFields' => true,
                        ]),
                    ]),
                ]),
                'fulfillment_address' => new Assert\Optional([
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
            'allowMissingFields' => false,
        ]));
    }
}
