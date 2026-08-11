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

use Magebit\AgenticCommerce\Api\Data\AddressInterface;
use Magebit\AgenticCommerce\Api\Data\AddressInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\AllowanceInterface;
use Magebit\AgenticCommerce\Api\Data\AllowanceInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\PaymentMethodInterface;
use Magebit\AgenticCommerce\Api\Data\PaymentMethodInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\Request\DelegatePaymentRequestInterface;
use Magebit\AgenticCommerce\Api\Data\RiskSignalInterface;
use Magebit\AgenticCommerce\Api\Data\RiskSignalInterfaceFactory;
use Magebit\AgenticCommerce\Model\Data\DataTransferObject;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Delegate Payment Request Data Transfer Object
 */
class DelegatePaymentRequest extends DataTransferObject implements DelegatePaymentRequestInterface
{
    /**
     * @param PaymentMethodInterfaceFactory $paymentMethodInterfaceFactory
     * @param AllowanceInterfaceFactory $allowanceInterfaceFactory
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param RiskSignalInterfaceFactory $riskSignalInterfaceFactory
     * @param array<mixed> $data
     */
    public function __construct(
        private readonly PaymentMethodInterfaceFactory $paymentMethodInterfaceFactory,
        private readonly AllowanceInterfaceFactory $allowanceInterfaceFactory,
        private readonly AddressInterfaceFactory $addressInterfaceFactory,
        private readonly RiskSignalInterfaceFactory $riskSignalInterfaceFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethod(): PaymentMethodInterface
    {
        return $this->getDataInstance('payment_method', PaymentMethodInterface::class, $this->paymentMethodInterfaceFactory->create(...));
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethod(PaymentMethodInterface $paymentMethod): DelegatePaymentRequestInterface
    {
        return $this->setData('payment_method', $paymentMethod);
    }

    /**
     * @inheritDoc
     */
    public function getAllowance(): AllowanceInterface
    {
        return $this->getDataInstance('allowance', AllowanceInterface::class, $this->allowanceInterfaceFactory->create(...));
    }

    /**
     * @inheritDoc
     */
    public function setAllowance(AllowanceInterface $allowance): DelegatePaymentRequestInterface
    {
        return $this->setData('allowance', $allowance);
    }

    /**
     * @inheritDoc
     */
    public function getBillingAddress(): ?AddressInterface
    {
        return $this->getDataInstance('billing_address', AddressInterface::class, $this->addressInterfaceFactory->create(...));
    }

    /**
     * @inheritDoc
     */
    public function setBillingAddress(?AddressInterface $billingAddress): DelegatePaymentRequestInterface
    {
        return $this->setData('billing_address', $billingAddress);
    }

    /**
     * @inheritDoc
     */
    public function getRiskSignals(): array
    {
        return $this->getDataInstanceArray('risk_signals', RiskSignalInterface::class, $this->riskSignalInterfaceFactory->create(...));
    }

    /**
     * @inheritDoc
     */
    public function setRiskSignals(array $riskSignals): DelegatePaymentRequestInterface
    {
        return $this->setData('risk_signals', $riskSignals);
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): array
    {
        $data = $this->getData('metadata');
        /** @var array<string, mixed> $result */
        $result = is_array($data) ? $data : [];
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function setMetadata(array $metadata): DelegatePaymentRequestInterface
    {
        return $this->setData('metadata', $metadata);
    }

    /**
     * Validation based on spec:
     * https://developers.openai.com/commerce/specs/payment
     *
     * @inheritDoc
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        // allowExtraFields stays true: ACP adds optional fields between releases; ignore, never reject.
        $metadata->addGetterConstraint('rawData', new Assert\Collection([
            'fields' => [
                'payment_method' => new Assert\Required([
                    new Assert\NotBlank(message: 'Payment method is required'),
                    new Assert\Type('array'),
                    new Assert\Collection([
                        'fields' => [
                            'type' => new Assert\Required([
                                new Assert\NotBlank(),
                                new Assert\Choice(['card'], message: 'Payment method type must be "card"'),
                            ]),
                            'card_number_type' => new Assert\Required([
                                new Assert\NotBlank(),
                                new Assert\Choice(['fpan', 'network_token'], message: 'Card number type must be "fpan" or "network_token"'),
                            ]),
                            'number' => new Assert\Required([
                                new Assert\NotBlank(message: 'Card number is required'),
                            ]),
                            'display_card_funding_type' => new Assert\Required([
                                new Assert\NotBlank(),
                                new Assert\Choice(['credit', 'debit', 'prepaid'], message: 'Display card funding type must be "credit", "debit", or "prepaid"'),
                            ]),
                            'metadata' => new Assert\Required([
                                new Assert\Type('array'),
                            ]),
                            // Optional fields
                            'exp_month' => new Assert\Optional([
                                new Assert\Length(max: 2),
                            ]),
                            'exp_year' => new Assert\Optional([
                                new Assert\Length(max: 4),
                            ]),
                            'name' => new Assert\Optional(),
                            'cvc' => new Assert\Optional([
                                new Assert\Length(max: 4),
                            ]),
                            'cryptogram' => new Assert\Optional(),
                            'eci_value' => new Assert\Optional(),
                            'checks_performed' => new Assert\Optional([
                                new Assert\Type('array'),
                            ]),
                            'iin' => new Assert\Optional([
                                new Assert\Length(max: 6),
                            ]),
                            'display_wallet_type' => new Assert\Optional(),
                            'display_brand' => new Assert\Optional(),
                            'display_last4' => new Assert\Optional([
                                new Assert\Length(max: 4),
                            ]),
                        ],
                        'allowExtraFields' => true,
                    ]),
                ]),
                'allowance' => new Assert\Required([
                    new Assert\NotBlank(message: 'Allowance is required'),
                    new Assert\Type('array'),
                    new Assert\Collection([
                        'fields' => [
                            'reason' => new Assert\Required([
                                new Assert\NotBlank(),
                                new Assert\Choice(['one_time'], message: 'Reason must be "one_time"'),
                            ]),
                            'max_amount' => new Assert\Required([
                                new Assert\NotBlank(),
                                new Assert\Type('int'),
                            ]),
                            'currency' => new Assert\Required([
                                new Assert\NotBlank(),
                                new Assert\Length(min: 3, max: 3),
                                new Assert\Regex('/^[a-z]{3}$/', message: 'Currency must be ISO-4217 lowercase (e.g., "usd")'),
                            ]),
                            'checkout_session_id' => new Assert\Required([
                                new Assert\NotBlank(),
                            ]),
                            'merchant_id' => new Assert\Required([
                                new Assert\NotBlank(),
                                new Assert\Length(max: 256),
                            ]),
                            'expires_at' => new Assert\Required([
                                new Assert\NotBlank(),
                                new Assert\Regex(
                                    pattern: '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?([+-]\d{2}:\d{2}|Z)$/',
                                    message: 'Expires at must be a valid RFC 3339 datetime string (e.g., "2025-10-09T07:20:50.52Z")'
                                ),
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
                                new Assert\Regex('/^[A-Z]{2}$/', message: 'Country must be ISO-3166-1 alpha-2 (e.g., "US")'),
                            ]),
                            'postal_code' => new Assert\Required([
                                new Assert\NotBlank(),
                                new Assert\Length(max: 20),
                            ]),
                        ],
                        'allowExtraFields' => true,
                    ]),
                ]),
                'risk_signals' => new Assert\Required([
                    new Assert\NotBlank(message: 'Risk signals are required'),
                    new Assert\Type('array'),
                    new Assert\All([
                        new Assert\Collection([
                            'fields' => [
                                'type' => new Assert\Required([
                                    new Assert\NotBlank(),
                                ]),
                                'score' => new Assert\Required([
                                    new Assert\NotBlank(),
                                    new Assert\Type('int'),
                                ]),
                                'action' => new Assert\Required([
                                    new Assert\NotBlank(),
                                    new Assert\Choice(['blocked', 'manual_review', 'authorized'], message: 'Action must be "blocked", "manual_review", or "authorized"'),
                                ]),
                            ],
                            'allowExtraFields' => true,
                        ]),
                    ]),
                ]),
                'metadata' => new Assert\Required([
                    new Assert\NotBlank(message: 'Metadata is required'),
                    new Assert\Type('array'),
                ]),
            ],
            'allowExtraFields' => true,
            'allowMissingFields' => false,
        ]));
    }
}
