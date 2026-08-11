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

use Magebit\AcpSpec\Api\AgenticCheckout\AddressInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\LineItemInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\CapabilitiesInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\MessageErrorInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\MessageInfoInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentDetailsInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\SelectedFulfillmentOptionInterface;
use Magebit\AgenticCommerce\Api\Data\Response\CheckoutSessionResponseInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\LinkInterface;
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
        return $this->getDataOf('buyer', BuyerInterface::class);
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
    public function getCapabilities(): ?CapabilitiesInterface
    {
        return $this->getDataOf('capabilities', CapabilitiesInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function setCapabilities(?CapabilitiesInterface $capabilities): CheckoutSessionResponseInterface
    {
        return $this->setData('capabilities', $capabilities);
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
        return $this->getDataListOf('line_items', LineItemInterface::class);
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
    public function getFulfillmentDetails(): ?FulfillmentDetailsInterface
    {
        return $this->getDataOf('fulfillment_details', FulfillmentDetailsInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function setFulfillmentDetails(
        ?FulfillmentDetailsInterface $fulfillmentDetails
    ): CheckoutSessionResponseInterface {
        return $this->setData('fulfillment_details', $fulfillmentDetails);
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
    public function getSelectedFulfillmentOptions(): array
    {
        return $this->getDataListOf('selected_fulfillment_options', SelectedFulfillmentOptionInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function setSelectedFulfillmentOptions(array $options): CheckoutSessionResponseInterface
    {
        return $this->setData('selected_fulfillment_options', $options);
    }

    /**
     * @inheritDoc
     */
    public function getTotals(): array
    {
        return $this->getDataListOf('totals', TotalInterface::class);
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
        return $this->getDataListOf('links', LinkInterface::class);
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
        $messages = [];

        // The spec types `messages` as a union, so both variants are accepted here.
        foreach ((array)($this->getData('messages') ?? []) as $message) {
            if ($message instanceof MessageInfoInterface || $message instanceof MessageErrorInterface) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * @inheritDoc
     */
    public function setMessages(array $messages): CheckoutSessionResponseInterface
    {
        return $this->setData('messages', $messages);
    }
}
