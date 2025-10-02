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
 * Payment Data interface
 */
interface PaymentDataInterface
{
    public const PROVIDER_STRIPE = 'stripe';

    /**
     * Get token
     *
     * @return string
     */
    public function getToken(): string;

    /**
     * Set token
     *
     * @param string $token
     * @return $this
     */
    public function setToken(string $token): self;

    /**
     * Get provider
     *
     * @return string
     */
    public function getProvider(): string;

    /**
     * Set provider
     *
     * @param string $provider
     * @return $this
     */
    public function setProvider(string $provider): self;

    /**
     * Get billing address
     *
     * @return \Magebit\AgenticCommerce\Api\Data\AddressInterface|null
     */
    public function getBillingAddress(): ?AddressInterface;

    /**
     * Set billing address
     *
     * @param \Magebit\AgenticCommerce\Api\Data\AddressInterface|null $address
     * @return $this
     */
    public function setBillingAddress(?AddressInterface $address): self;
}
