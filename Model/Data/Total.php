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

use Magebit\AgenticCommerce\Api\Data\TotalInterface;
use Magento\Framework\DataObject;

/**
 * Total Data Transfer Object
 */
class Total extends DataObject implements TotalInterface
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
    public function setType(string $type): TotalInterface
    {
        return $this->setData('type', $type);
    }

    /**
     * @inheritDoc
     */
    public function getDisplayText(): string
    {
        return (string) $this->getData('display_text');
    }

    /**
     * @inheritDoc
     */
    public function setDisplayText(string $text): TotalInterface
    {
        return $this->setData('display_text', $text);
    }

    /**
     * @inheritDoc
     */
    public function getAmount(): int
    {
        return (int) $this->getData('amount');
    }

    /**
     * @inheritDoc
     */
    public function setAmount(int $amount): TotalInterface
    {
        return $this->setData('amount', $amount);
    }
}
