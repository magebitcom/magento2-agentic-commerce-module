<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data;

use Magebit\AgenticCommerce\Api\Data\ItemInterface;
use Magebit\AgenticCommerce\Api\Data\LineItemInterface;
use Magebit\AgenticCommerce\Model\Data\DataTransferObject;

/**
 * Line Item Data Transfer Object
 */
class LineItem extends DataTransferObject implements LineItemInterface
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
    public function setId(string $id): LineItemInterface
    {
        return $this->setData('id', $id);
    }

    /**
     * @inheritDoc
     */
    public function getItem(): ItemInterface
    {
        $item = $this->getData('item');
        if ($item instanceof ItemInterface) {
            return $item;
        }
        return Item::from((array) $item);
    }

    /**
     * @inheritDoc
     */
    public function setItem(ItemInterface $item): LineItemInterface
    {
        return $this->setData('item', $item);
    }

    /**
     * @inheritDoc
     */
    public function getBaseAmount(): int
    {
        return (int) $this->getData('base_amount');
    }

    /**
     * @inheritDoc
     */
    public function setBaseAmount(int $amount): LineItemInterface
    {
        return $this->setData('base_amount', $amount);
    }

    /**
     * @inheritDoc
     */
    public function getDiscount(): int
    {
        return (int) $this->getData('discount');
    }

    /**
     * @inheritDoc
     */
    public function setDiscount(int $discount): LineItemInterface
    {
        return $this->setData('discount', $discount);
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
    public function setSubtotal(int $subtotal): LineItemInterface
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
    public function setTax(int $tax): LineItemInterface
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
    public function setTotal(int $total): LineItemInterface
    {
        return $this->setData('total', $total);
    }
}
