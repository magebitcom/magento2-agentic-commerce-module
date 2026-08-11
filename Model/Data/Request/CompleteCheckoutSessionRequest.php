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
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInterfaceFactory;
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
     * @param PaymentDataInterfaceFactory $paymentDataInterfaceFactory
     * @param BuyerInterfaceFactory $buyerInterfaceFactory
     * @param array<mixed> $data
     */
    public function __construct(
        private readonly PaymentDataInterfaceFactory $paymentDataInterfaceFactory,
        private readonly BuyerInterfaceFactory $buyerInterfaceFactory,
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
     * @inheritDoc
     */
    public function getPaymentData(): PaymentDataInterface
    {
        $data = $this->getDataInstance(
            'payment_data',
            PaymentDataInterface::class,
            $this->paymentDataInterfaceFactory->create(...)
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
                            'token' => new Assert\Required([
                                new Assert\NotBlank(message: 'Payment token is required'),
                            ]),
                            'provider' => new Assert\Required([
                                new Assert\NotBlank(message: 'Payment provider is required'),
                                new Assert\Choice(
                                    ['stripe'],
                                    message: 'Payment provider must be "stripe"'
                                ),
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
