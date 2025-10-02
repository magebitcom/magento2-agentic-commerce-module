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

interface OrderInterface
{
    /**
     * Get order ID
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Set order ID
     *
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self;

    /**
     * Get checkout session ID
     *
     * @return string
     */
    public function getCheckoutSessionId(): string;

    /**
     * Set checkout session ID
     *
     * @param string $checkoutSessionId
     * @return $this
     */
    public function setCheckoutSessionId(string $checkoutSessionId): self;

    /**
     * Get permalink URL
     *
     * @return string
     */
    public function getPermalinkUrl(): string;

    /**
     * Set permalink URL
     *
     * @param string $url
     * @return $this
     */
    public function setPermalinkUrl(string $url): self;
}
