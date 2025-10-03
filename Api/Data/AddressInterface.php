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
 * Address interface
 */
interface AddressInterface
{
    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * Get line one
     *
     * @return string
     */
    public function getLineOne(): string;

    /**
     * Set line one
     *
     * @param string $lineOne
     * @return $this
     */
    public function setLineOne(string $lineOne): self;

    /**
     * Get line two
     *
     * @return string|null
     */
    public function getLineTwo(): ?string;

    /**
     * Set line two
     *
     * @param string|null $lineTwo
     * @return $this
     */
    public function setLineTwo(?string $lineTwo): self;

    /**
     * Get city
     *
     * @return string
     */
    public function getCity(): string;

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity(string $city): self;

    /**
     * Get state
     *
     * @return string
     */
    public function getState(): string;

    /**
     * Set state
     *
     * @param string $state
     * @return $this
     */
    public function setState(string $state): self;

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry(): string;

    /**
     * Set country
     *
     * @param string $country
     * @return $this
     */
    public function setCountry(string $country): self;

    /**
     * Get postal code
     *
     * @return string
     */
    public function getPostalCode(): string;

    /**
     * Set postal code
     *
     * @param string $postalCode
     * @return $this
     */
    public function setPostalCode(string $postalCode): self;
}
