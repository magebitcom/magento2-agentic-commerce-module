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

use Magebit\AcpSpec\Data\AgenticCheckout\Item as SpecItem;
use Magebit\AgenticCommerce\Api\Data\ItemInterface;

/**
 * The generated item plus the `quantity` the spec's schema omits.
 */
class Item extends SpecItem implements ItemInterface
{
    private const KEY_QUANTITY = 'quantity';

    /**
     * @inheritDoc
     */
    public function getQuantity(): int
    {
        return $this->requireInt(self::KEY_QUANTITY);
    }

    /**
     * @inheritDoc
     */
    public function setQuantity(int $quantity): ItemInterface
    {
        return $this->set(self::KEY_QUANTITY, $quantity);
    }
}
