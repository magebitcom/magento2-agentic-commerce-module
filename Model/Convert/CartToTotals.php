<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AgenticCommerce\Api\Data\TotalInterface;
use Magebit\AgenticCommerce\Api\Data\TotalInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magebit\AgenticCommerce\Model\Convert\ConvertPrice;

class CartToTotals
{
    /**
     * @param TotalInterfaceFactory $totalInterfaceFactory
     * @param ConvertPrice $convertPrice
     */
    public function __construct(
        protected readonly TotalInterfaceFactory $totalInterfaceFactory,
        protected readonly ConvertPrice $convertPrice,
    ) {
    }

    /**
     * @param Quote $cart
     * @return TotalInterface[]
     */
    public function execute(Quote $cart): array
    {
        $totals = [];

        foreach ($cart->getTotals() as $cartTotal) {
            /** @var TotalInterface $total */
            $total = $this->totalInterfaceFactory->create();

            $total->setType($cartTotal->getCode());
            $total->setDisplayText((string) $cartTotal->getTitle());
            $total->setAmount($this->convertPrice->execute($cartTotal->getValue()));
            $totals[] = $total;
        }

        return $totals;
    }
}
