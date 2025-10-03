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
 * Fulfillment Option Shipping interface
 */
interface FulfillmentOptionShippingInterface extends FulfillmentOptionInterface
{
    /**
     * Get carrier
     *
     * @return string|null
     */
    public function getCarrier(): ?string;

    /**
     * Set carrier
     *
     * @param string|null $carrier
     * @return $this
     */
    public function setCarrier(?string $carrier): self;

    /**
     * Get earliest delivery time
     *
     * @return string|null ISO 8601 date-time
     */
    public function getEarliestDeliveryTime(): ?string;

    /**
     * Set earliest delivery time
     *
     * @param string|null $time ISO 8601 date-time
     * @return $this
     */
    public function setEarliestDeliveryTime(?string $time): self;

    /**
     * Get latest delivery time
     *
     * @return string|null ISO 8601 date-time
     */
    public function getLatestDeliveryTime(): ?string;

    /**
     * Set latest delivery time
     *
     * @param string|null $time ISO 8601 date-time
     * @return $this
     */
    public function setLatestDeliveryTime(?string $time): self;
}
