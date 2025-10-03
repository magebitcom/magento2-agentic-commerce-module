<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Response;

use Magebit\AgenticCommerce\Api\Data\AddressInterface;
use Magebit\AgenticCommerce\Api\Data\BuyerInterface;
use Magebit\AgenticCommerce\Api\Data\PaymentProviderInterface;
use Magebit\AgenticCommerce\Api\Data\Response\CheckoutSessionResponseInterface;
use Magebit\AgenticCommerce\Model\Data\DataTransferObject;

/**
 * Checkout Session Response Data Transfer Object
 */
class CheckoutSessionResponse extends DataTransferObject implements CheckoutSessionResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return (string) $this->getData('id');
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id): CheckoutSessionResponseInterface
    {
        return $this->setData('id', $id);
    }

    /**
     * @inheritDoc
     */
    public function getBuyer(): ?BuyerInterface
    {
        return $this->getData('buyer');
    }

    /**
     * @inheritDoc
     */
    public function setBuyer(?BuyerInterface $buyer): CheckoutSessionResponseInterface
    {
        return $this->setData('buyer', $buyer);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentProvider(): ?PaymentProviderInterface
    {
        return $this->getData('payment_provider');
    }

    /**
     * @inheritDoc
     */
    public function setPaymentProvider(?PaymentProviderInterface $paymentProvider): CheckoutSessionResponseInterface
    {
        return $this->setData('payment_provider', $paymentProvider);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return (string) $this->getData('status');
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status): CheckoutSessionResponseInterface
    {
        return $this->setData('status', $status);
    }

    /**
     * @inheritDoc
     */
    public function getCurrency(): string
    {
        return (string) $this->getData('currency');
    }

    /**
     * @inheritDoc
     */
    public function setCurrency(string $currency): CheckoutSessionResponseInterface
    {
        return $this->setData('currency', $currency);
    }

    /**
     * @inheritDoc
     */
    public function getLineItems(): array
    {
        return $this->getData('line_items');
    }

    /**
     * @inheritDoc
     */
    public function setLineItems(array $lineItems): CheckoutSessionResponseInterface
    {
        return $this->setData('line_items', $lineItems);
    }

    /**
     * @inheritDoc
     */
    public function getFulfillmentAddress(): ?AddressInterface
    {
        return $this->getData('fulfillment_address');
    }

    /**
     * @inheritDoc
     */
    public function setFulfillmentAddress(?AddressInterface $address): CheckoutSessionResponseInterface
    {
        return $this->setData('fulfillment_address', $address);
    }

    /**
     * @inheritDoc
     */
    public function getFulfillmentOptions(): array
    {
        return $this->getData('fulfillment_options');
    }

    /**
     * @inheritDoc
     */
    public function setFulfillmentOptions(array $options): CheckoutSessionResponseInterface
    {
        return $this->setData('fulfillment_options', $options);
    }

    /**
     * @inheritDoc
     */
    public function getFulfillmentOptionId(): ?string
    {
        return $this->getData('fulfillment_option_id');
    }

    /**
     * @inheritDoc
     */
    public function setFulfillmentOptionId(?string $fulfillmentOptionId): CheckoutSessionResponseInterface
    {
        return $this->setData('fulfillment_option_id', $fulfillmentOptionId);
    }

    /**
     * @inheritDoc
     */
    public function getTotals(): array
    {
        return $this->getData('totals');
    }

    /**
     * @inheritDoc
     */
    public function setTotals(array $totals): CheckoutSessionResponseInterface
    {
        return $this->setData('totals', $totals);
    }

    /**
     * @inheritDoc
     */
    public function getLinks(): array
    {
        return $this->getData('links');
    }

    /**
     * @inheritDoc
     */
    public function setLinks(array $links): CheckoutSessionResponseInterface
    {
        return $this->setData('links', $links);
    }

    /**
     * @inheritDoc
     */
    public function getMessages(): array
    {
        $messages = $this->getData('messages');
        return is_array($messages) ? $messages : [];
    }

    /**
     * @inheritDoc
     */
    public function setMessages(array $messages): CheckoutSessionResponseInterface
    {
        return $this->setData('messages', $messages);
    }
}
