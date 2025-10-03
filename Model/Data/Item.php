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
use Magento\Framework\DataObject;

/**
 * Item Data Transfer Object
 */
class Item extends DataObject implements ItemInterface
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
    public function setId(string $id): ItemInterface
    {
        return $this->setData('id', $id);
    }

    /**
     * @inheritDoc
     */
    public function getQuantity(): int
    {
        return (int) $this->getData('quantity');
    }

    /**
     * @inheritDoc
     */
    public function setQuantity(int $quantity): ItemInterface
    {
        return $this->setData('quantity', $quantity);
    }
}
