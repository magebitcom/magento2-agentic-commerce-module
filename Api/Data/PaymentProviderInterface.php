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
 * Payment Provider interface
 */
interface PaymentProviderInterface
{
    public const PROVIDER_STRIPE = 'stripe';
    public const PAYMENT_METHOD_CARD = 'card';

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
     * Get supported payment methods
     *
     * @return string[]
     */
    public function getSupportedPaymentMethods(): array;

    /**
     * Set supported payment methods
     *
     * @param string[] $methods
     * @return $this
     */
    public function setSupportedPaymentMethods(array $methods): self;
}
