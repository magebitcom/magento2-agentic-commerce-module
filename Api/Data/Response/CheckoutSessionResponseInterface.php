<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data\Response;

use Magebit\AgenticCommerce\Api\Data\AddressInterface;
use Magebit\AgenticCommerce\Api\Data\BuyerInterface;
use Magebit\AgenticCommerce\Api\Data\FulfillmentOptionInterface;
use Magebit\AgenticCommerce\Api\Data\LineItemInterface;
use Magebit\AgenticCommerce\Api\Data\LinkInterface;
use Magebit\AgenticCommerce\Api\Data\MessageInterface;
use Magebit\AgenticCommerce\Api\Data\PaymentProviderInterface;
use Magebit\AgenticCommerce\Api\Data\TotalInterface;

interface CheckoutSessionResponseInterface
{
    public const STATUS_NOT_READY_FOR_PAYMENT = 'not_ready_for_payment';
    public const STATUS_READY_FOR_PAYMENT = 'ready_for_payment';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_IN_PROGRESS = 'in_progress';

    /**
     * Get ID
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Set ID
     *
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self;

    /**
     * Get buyer
     *
     * @return \Magebit\AgenticCommerce\Api\Data\BuyerInterface|null
     */
    public function getBuyer(): ?BuyerInterface;

    /**
     * Set buyer
     *
     * @param \Magebit\AgenticCommerce\Api\Data\BuyerInterface|null $buyer
     * @return $this
     */
    public function setBuyer(?BuyerInterface $buyer): self;

    /**
     * Get payment provider
     *
     * @return \Magebit\AgenticCommerce\Api\Data\PaymentProviderInterface|null
     */
    public function getPaymentProvider(): ?PaymentProviderInterface;

    /**
     * Set payment provider
     *
     * @param \Magebit\AgenticCommerce\Api\Data\PaymentProviderInterface|null $paymentProvider
     * @return $this
     */
    public function setPaymentProvider(?PaymentProviderInterface $paymentProvider): self;

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self;

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency(): string;

    /**
     * Set currency
     *
     * @param string $currency
     * @return $this
     */
    public function setCurrency(string $currency): self;

    /**
     * Get line items
     *
     * @return \Magebit\AgenticCommerce\Api\Data\LineItemInterface[]
     */
    public function getLineItems(): array;

    /**
     * Set line items
     *
     * @param \Magebit\AgenticCommerce\Api\Data\LineItemInterface[] $lineItems
     * @return $this
     */
    public function setLineItems(array $lineItems): self;

    /**
     * Get fulfillment address
     *
     * @return \Magebit\AgenticCommerce\Api\Data\AddressInterface|null
     */
    public function getFulfillmentAddress(): ?AddressInterface;

    /**
     * Set fulfillment address
     *
     * @param \Magebit\AgenticCommerce\Api\Data\AddressInterface|null $address
     * @return $this
     */
    public function setFulfillmentAddress(?AddressInterface $address): self;

    /**
     * Get fulfillment options
     *
     * @return \Magebit\AgenticCommerce\Api\Data\FulfillmentOptionInterface[]
     */
    public function getFulfillmentOptions(): array;

    /**
     * Set fulfillment options
     *
     * @param \Magebit\AgenticCommerce\Api\Data\FulfillmentOptionInterface[] $options
     * @return $this
     */
    public function setFulfillmentOptions(array $options): self;

    /**
     * Get fulfillment option ID
     *
     * @return string|null
     */
    public function getFulfillmentOptionId(): ?string;

    /**
     * Set fulfillment option ID
     *
     * @param string|null $fulfillmentOptionId
     * @return $this
     */
    public function setFulfillmentOptionId(?string $fulfillmentOptionId): self;

    /**
     * Get totals
     *
     * @return \Magebit\AgenticCommerce\Api\Data\TotalInterface[]
     */
    public function getTotals(): array;

    /**
     * Set totals
     *
     * @param \Magebit\AgenticCommerce\Api\Data\TotalInterface[] $totals
     * @return $this
     */
    public function setTotals(array $totals): self;

    /**
     * Get links
     *
     * @return \Magebit\AgenticCommerce\Api\Data\LinkInterface[]
     */
    public function getLinks(): array;

    /**
     * Set links
     *
     * @param \Magebit\AgenticCommerce\Api\Data\LinkInterface[] $links
     * @return $this
     */
    public function setLinks(array $links): self;

    /**
     * Get messages
     *
     * @return \Magebit\AgenticCommerce\Api\Data\MessageInterface[]
     */
    public function getMessages(): array;

    /**
     * Set messages
     *
     * @param \Magebit\AgenticCommerce\Api\Data\MessageInterface[] $messages
     * @return $this
     */
    public function setMessages(array $messages): self;
}
