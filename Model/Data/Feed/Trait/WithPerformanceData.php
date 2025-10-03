<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Feed\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\PerformanceInterface;

trait WithPerformanceData
{
    /**
     * Get the popularity score
     *
     * @return float|null
     */
    public function getPopularityScore(): ?float
    {
        return $this->getData(PerformanceInterface::POPULARITY_SCORE);
    }

    /**
     * Get the return rate
     *
     * @return float|null
     */
    public function getReturnRate(): ?float
    {
        return $this->getData(PerformanceInterface::RETURN_RATE);
    }
}
