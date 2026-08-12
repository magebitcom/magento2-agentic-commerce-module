<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magebit\AgenticCore\Model\Money\MinorUnits;

class CartToTotals
{
    /**
     * @param TotalInterfaceFactory $totalInterfaceFactory
     * @param MinorUnits $minorUnits
     */
    public function __construct(
        protected readonly TotalInterfaceFactory $totalInterfaceFactory,
        protected readonly MinorUnits $minorUnits,
    ) {
    }

    /**
     * @param Quote $cart
     * @return TotalInterface[]
     */
    public function execute(Quote $cart): array
    {
        $totals = [];
        $currencyCode = $cart->getCurrency()?->getStoreCurrencyCode() ?? 'USD';

        foreach ($cart->getTotals() as $cartTotal) {
            /** @var TotalInterface $total */
            $total = $this->totalInterfaceFactory->create();

            $total->setType($cartTotal->getCode());
            $total->setDisplayText((string) $cartTotal->getTitle());
            $total->setAmount($this->minorUnits->convert($cartTotal->getValue(), $currencyCode));
            $totals[] = $total;
        }

        return $totals;
    }
}
