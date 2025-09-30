<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

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

