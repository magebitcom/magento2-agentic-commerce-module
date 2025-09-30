<?php

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\ReturnsInterface;

trait WithReturnsData
{
    /**
     * Get the return policy
     *
     * @return string
     */
    public function getReturnPolicy(): string
    {
        return $this->getData(ReturnsInterface::RETURN_POLICY);
    }

    /**
     * Get the return window
     *
     * @return int
     */
    public function getReturnWindow(): int
    {
        return $this->getData(ReturnsInterface::RETURN_WINDOW);
    }
}
