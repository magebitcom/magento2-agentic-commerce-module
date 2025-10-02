<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Feed\Trait;

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
