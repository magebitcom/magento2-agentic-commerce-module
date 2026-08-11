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

use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterface;
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
    public function getQuantity(): int
    {
        return $this->getDataInt('quantity');
    }

    /**
     * @inheritDoc
     */
    public function setQuantity(int $quantity): LineItemInterface
    {
        return $this->setData('quantity', $quantity);
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
    public function setTotals(array $totals): LineItemInterface
    {
        return $this->setData('totals', $totals);
    }
}
