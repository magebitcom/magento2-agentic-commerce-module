<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Api\Data\Spec;

interface OpenAIFlagsInterface
{
    public const ENABLE_SEARCH = 'enable_search';
    public const ENABLE_CHECKOUT = 'enable_checkout';

    /**
     * Get the enable search
     *
     * @return bool
     */
    public function getEnableSearch(): bool;

    /**
     * Get the enable checkout
     *
     * @return bool
     */
    public function getEnableCheckout(): bool;
}
