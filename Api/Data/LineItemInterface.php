<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
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
     * @return int
     */
    public function getQuantity(): int;

    /**
     * @param int $quantity
     * @return self
     */
    public function setQuantity(int $quantity): self;

    /**
     * The money breakdown, replacing the flat base_amount/discount/subtotal/tax/total fields.
     *
     * @return \Magebit\AcpSpec\Api\AgenticCheckout\TotalInterface[]
     */
    public function getTotals(): array;

    /**
     * @param \Magebit\AcpSpec\Api\AgenticCheckout\TotalInterface[] $totals
     * @return self
     */
    public function setTotals(array $totals): self;
}
