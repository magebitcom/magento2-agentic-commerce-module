<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data;

/**
 * Total interface
 */
interface TotalInterface
{
    public const TYPE_ITEMS_BASE_AMOUNT = 'items_base_amount';
    public const TYPE_ITEMS_DISCOUNT = 'items_discount';
    public const TYPE_SUBTOTAL = 'subtotal';
    public const TYPE_DISCOUNT = 'discount';
    public const TYPE_FULFILLMENT = 'fulfillment';
    public const TYPE_TAX = 'tax';
    public const TYPE_FEE = 'fee';
    public const TYPE_TOTAL = 'total';

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self;

    /**
     * Get display text
     *
     * @return string
     */
    public function getDisplayText(): string;

    /**
     * Set display text
     *
     * @param string $text
     * @return $this
     */
    public function setDisplayText(string $text): self;

    /**
     * Get amount (in cents)
     *
     * @return int
     */
    public function getAmount(): int;

    /**
     * Set amount (in cents)
     *
     * @param int $amount
     * @return $this
     */
    public function setAmount(int $amount): self;
}
