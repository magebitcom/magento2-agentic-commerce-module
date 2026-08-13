<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data\Request;

use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\SelectedFulfillmentOptionInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\SelectedFulfillmentOptionInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\ItemInterface;
use Magebit\AgenticCommerce\Api\Data\Request\UpdateCheckoutSessionRequestInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscountsRequestInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscountsRequestInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\ItemInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\ValidatableDataInterface;
use Magebit\AgenticCommerce\Model\Data\DataTransferObject;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCheckoutSessionRequest extends DataTransferObject implements
    UpdateCheckoutSessionRequestInterface,
    ValidatableDataInterface
{
    /**
     * @param ItemInterfaceFactory $itemInterfaceFactory
     * @param FulfillmentDetailsBuilder $fulfillmentDetailsBuilder
     * @param SelectedFulfillmentOptionInterfaceFactory $selectedFulfillmentOptionInterfaceFactory
     * @param BuyerInterfaceFactory $buyerInterfaceFactory
     * @param DiscountsRequestInterfaceFactory $discountsRequestFactory
     * @param array<mixed> $data
     */
    public function __construct(
        private readonly ItemInterfaceFactory $itemInterfaceFactory,
        private readonly FulfillmentDetailsBuilder $fulfillmentDetailsBuilder,
        private readonly SelectedFulfillmentOptionInterfaceFactory $selectedFulfillmentOptionInterfaceFactory,
        private readonly BuyerInterfaceFactory $buyerInterfaceFactory,
        private readonly DiscountsRequestInterfaceFactory $discountsRequestFactory,
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
     * @return DiscountsRequestInterface|null
     */
    public function getDiscounts(): ?DiscountsRequestInterface
    {
        $discounts = $this->getDataInstance(
            'discounts',
            DiscountsRequestInterface::class,
            $this->discountsRequestFactory->create(...)
        );

        return $discounts instanceof DiscountsRequestInterface ? $discounts : null;
    }

    /**
     * @inheritDoc
     */
    public function getSelectedFulfillmentOptions(): array
    {
        return $this->getDataInstanceArray(
            'selected_fulfillment_options',
            SelectedFulfillmentOptionInterface::class,
            $this->selectedFulfillmentOptionInterfaceFactory->create(...)
        );
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
                'line_items' => new Assert\Optional([
                    new Assert\Type('array'),
                    new Assert\All([
                        new Assert\Collection([
                            'fields' => [
                                'id' => new Assert\Required([
                                    new Assert\NotBlank(message: 'Line item id is required'),
                                ]),
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
                                    'fields' => CreateCheckoutSessionRequest::addressFields(),
                                    'allowExtraFields' => true,
                                ]),
                            ]),
                        ],
                        'allowExtraFields' => true,
                    ]),
                ]),
                'selected_fulfillment_options' => new Assert\Optional([
                    new Assert\Type('array'),
                    new Assert\All([
                        new Assert\Collection([
                            'fields' => [
                                'type' => new Assert\Required([new Assert\NotBlank()]),
                                'option_id' => new Assert\Required([new Assert\NotBlank()]),
                                'item_ids' => new Assert\Required([new Assert\Type('array')]),
                            ],
                            'allowExtraFields' => true,
                        ]),
                    ]),
                ]),
            ],
            'allowExtraFields' => true,
            'allowMissingFields' => true,
        ]));
    }
}
