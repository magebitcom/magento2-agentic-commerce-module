<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data\Spec;

interface ReturnsInterface
{
    public const RETURN_POLICY = 'return_policy';
    public const RETURN_WINDOW = 'return_window';

    /**
     * Get the return policy
     *
     * @return string
     */
    public function getReturnPolicy(): string;

    /**
     * Get the return window
     *
     * @return int
     */
    public function getReturnWindow(): int;
}
