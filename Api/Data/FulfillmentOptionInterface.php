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
 * Base Fulfillment Option interface
 */
interface FulfillmentOptionInterface
{
    public const TYPE_SHIPPING = 'shipping';
    public const TYPE_DIGITAL = 'digital';

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
     * Get ID
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Set ID
     *
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self;

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self;

    /**
     * Get subtitle
     *
     * @return string|null
     */
    public function getSubtitle(): ?string;

    /**
     * Set subtitle
     *
     * @param string|null $subtitle
     * @return $this
     */
    public function setSubtitle(?string $subtitle): self;

    /**
     * Get subtotal
     *
     * @return int
     */
    public function getSubtotal(): int;

    /**
     * Set subtotal
     *
     * @param int $subtotal
     * @return $this
     */
    public function setSubtotal(int $subtotal): self;

    /**
     * Get tax
     *
     * @return int
     */
    public function getTax(): int;

    /**
     * Set tax
     *
     * @param int $tax
     * @return $this
     */
    public function setTax(int $tax): self;

    /**
     * Get total
     *
     * @return int
     */
    public function getTotal(): int;

    /**
     * Set total
     *
     * @param int $total
     * @return $this
     */
    public function setTotal(int $total): self;
}
