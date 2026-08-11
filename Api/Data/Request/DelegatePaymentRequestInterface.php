<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data\Request;

use Magebit\AcpSpec\Api\AgenticCheckout\AddressInterface;
use Magebit\AcpSpec\Api\DelegatePayment\AllowanceInterface;
use Magebit\AcpSpec\Api\DelegatePayment\PaymentMethodCardInterface;
use Magebit\AgenticCommerce\Api\Data\ValidatableDataInterface;

/**
 * Delegate Payment Request interface for OpenAI Agentic Commerce
 *
 * Represents the structure of a delegated payment request where OpenAI securely shares
 * payment details with the merchant or its designated payment service provider (PSP)
 */
interface DelegatePaymentRequestInterface extends RequestInterface, ValidatableDataInterface
{
    /**
     * Get payment method
     *
     * @return \Magebit\AcpSpec\Api\DelegatePayment\PaymentMethodCardInterface
     */
    public function getPaymentMethod(): PaymentMethodCardInterface;

    /**
     * Set payment method
     *
     * @param \Magebit\AcpSpec\Api\DelegatePayment\PaymentMethodCardInterface $paymentMethod
     * @return $this
     */
    public function setPaymentMethod(PaymentMethodCardInterface $paymentMethod): self;

    /**
     * Get allowance
     *
     * @return \Magebit\AcpSpec\Api\DelegatePayment\AllowanceInterface
     */
    public function getAllowance(): AllowanceInterface;

    /**
     * Set allowance
     *
     * @param \Magebit\AcpSpec\Api\DelegatePayment\AllowanceInterface $allowance
     * @return $this
     */
    public function setAllowance(AllowanceInterface $allowance): self;

    /**
     * Get billing address
     *
     * @return \Magebit\AcpSpec\Api\AgenticCheckout\AddressInterface|null
     */
    public function getBillingAddress(): ?AddressInterface;

    /**
     * Set billing address
     *
     * @param \Magebit\AcpSpec\Api\AgenticCheckout\AddressInterface|null $billingAddress
     * @return $this
     */
    public function setBillingAddress(?AddressInterface $billingAddress): self;

    /**
     * Get risk signals
     *
     * @return \Magebit\AcpSpec\Api\DelegatePayment\RiskSignalInterface[]
     */
    public function getRiskSignals(): array;

    /**
     * Set risk signals
     *
     * @param \Magebit\AcpSpec\Api\DelegatePayment\RiskSignalInterface[] $riskSignals
     * @return $this
     */
    public function setRiskSignals(array $riskSignals): self;

    /**
     * Get metadata
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array;

    /**
     * Set metadata
     *
     * @param array<string, mixed> $metadata
     * @return $this
     */
    public function setMetadata(array $metadata): self;
}
