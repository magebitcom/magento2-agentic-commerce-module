<?php

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
