<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Convert;

class ConvertPrice
{
    /**
     * @param float $price
     * @return int
     */
    public function execute(float $price): int
    {
        return (int) ($price * 100);
    }
}
