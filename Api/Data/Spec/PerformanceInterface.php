<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data\Spec;

interface PerformanceInterface
{
    public const POPULARITY_SCORE = 'popularity_score';
    public const RETURN_RATE = 'return_rate';

    /**
     * Get the popularity score
     *
     * @return float|null
     */
    public function getPopularityScore(): ?float;

    /**
     * Get the return rate
     *
     * @return float|null
     */
    public function getReturnRate(): ?float;
}

