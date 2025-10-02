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
 * Line Item interface
 */
interface LineItemInterface
{
    /**
     * Get line item ID
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Set line item ID
     *
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self;

    /**
     * Get item
     *
     * @return \Magebit\AgenticCommerce\Api\Data\ItemInterface
     */
    public function getItem(): ItemInterface;

    /**
     * Set item
     *
     * @param \Magebit\AgenticCommerce\Api\Data\ItemInterface $item
     * @return $this
     */
    public function setItem(ItemInterface $item): self;

    /**
     * Get base amount (in cents)
     *
     * @return int
     */
    public function getBaseAmount(): int;

    /**
     * Set base amount (in cents)
     *
     * @param int $amount
     * @return $this
     */
    public function setBaseAmount(int $amount): self;

    /**
     * Get discount (in cents)
     *
     * @return int
     */
    public function getDiscount(): int;

    /**
     * Set discount (in cents)
     *
     * @param int $discount
     * @return $this
     */
    public function setDiscount(int $discount): self;

    /**
     * Get subtotal (in cents)
     *
     * @return int
     */
    public function getSubtotal(): int;

    /**
     * Set subtotal (in cents)
     *
     * @param int $subtotal
     * @return $this
     */
    public function setSubtotal(int $subtotal): self;

    /**
     * Get tax (in cents)
     *
     * @return int
     */
    public function getTax(): int;

    /**
     * Set tax (in cents)
     *
     * @param int $tax
     * @return $this
     */
    public function setTax(int $tax): self;

    /**
     * Get total (in cents)
     *
     * @return int
     */
    public function getTotal(): int;

    /**
     * Set total (in cents)
     *
     * @param int $total
     * @return $this
     */
    public function setTotal(int $total): self;
}
