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
 * Buyer interface
 */
interface BuyerInterface
{
    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName(): string;

    /**
     * Set first name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstName(string $firstName): self;

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName(): string;

    /**
     * Set last name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastName(string $lastName): self;

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self;

    /**
     * Get phone number
     *
     * @return string|null
     */
    public function getPhoneNumber(): ?string;

    /**
     * Set phone number
     *
     * @param string|null $phoneNumber
     * @return $this
     */
    public function setPhoneNumber(?string $phoneNumber): self;
}
