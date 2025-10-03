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
 * Link interface
 */
interface LinkInterface
{
    public const TYPE_TERMS_OF_USE = 'terms_of_use';
    public const TYPE_PRIVACY_POLICY = 'privacy_policy';
    public const TYPE_SELLER_SHOP_POLICIES = 'seller_shop_policies';

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
     * Get URL
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Set URL
     *
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): self;
}
