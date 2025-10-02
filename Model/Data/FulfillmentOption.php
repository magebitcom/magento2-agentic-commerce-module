<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data;

use Magebit\AgenticCommerce\Api\Data\FulfillmentOptionInterface;
use Magento\Framework\DataObject;

/**
 * Fulfillment Option Data Transfer Object
 */
class FulfillmentOption extends DataObject implements FulfillmentOptionInterface
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return (string) $this->getData('type');
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): FulfillmentOptionInterface
    {
        return $this->setData('type', $type);
    }

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
    public function setId(string $id): FulfillmentOptionInterface
    {
        return $this->setData('id', $id);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return (string) $this->getData('title');
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title): FulfillmentOptionInterface
    {
        return $this->setData('title', $title);
    }

    /**
     * @inheritDoc
     */
    public function getSubtitle(): ?string
    {
        return $this->getData('subtitle');
    }

    /**
     * @inheritDoc
     */
    public function setSubtitle(?string $subtitle): FulfillmentOptionInterface
    {
        return $this->setData('subtitle', $subtitle);
    }

    /**
     * @inheritDoc
     */
    public function getSubtotal(): int
    {
        return (int) $this->getData('subtotal');
    }

    /**
     * @inheritDoc
     */
    public function setSubtotal(int $subtotal): FulfillmentOptionInterface
    {
        return $this->setData('subtotal', $subtotal);
    }

    /**
     * @inheritDoc
     */
    public function getTax(): int
    {
        return (int) $this->getData('tax');
    }

    /**
     * @inheritDoc
     */
    public function setTax(int $tax): FulfillmentOptionInterface
    {
        return $this->setData('tax', $tax);
    }

    /**
     * @inheritDoc
     */
    public function getTotal(): int
    {
        return (int) $this->getData('total');
    }

    /**
     * @inheritDoc
     */
    public function setTotal(int $total): FulfillmentOptionInterface
    {
        return $this->setData('total', $total);
    }
}
