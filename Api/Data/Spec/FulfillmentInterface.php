<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Api\Data\Spec;

interface FulfillmentInterface
{
    public const SHIPPING = 'shipping';
    public const DELIVERY_ESTIMATE = 'delivery_estimate';

    /**
     * Get the shipping
     *
     * @return string
     */
    public function getShipping(): string;

    /**
     * Get the delivery estimate
     *
     * @return string|null
     */
    public function getDeliveryEstimate(): ?string;
}

