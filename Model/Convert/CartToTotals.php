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
use Magebit\AgenticCore\Model\Total\TypeLabel;

class CartToTotals
{
    /**
     * @param TotalInterfaceFactory $totalInterfaceFactory
     * @param MinorUnits $minorUnits
     * @param TypeLabel $typeLabel
     * @param array<string, string> $typeMapping Magento total code to spec total type
     */
    public function __construct(
        protected readonly TotalInterfaceFactory $totalInterfaceFactory,
        protected readonly MinorUnits $minorUnits,
        protected readonly TypeLabel $typeLabel,
        protected readonly array $typeMapping = [],
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
            $type = $this->mapType((string) $cartTotal->getCode());

            // A code the spec has no member for is dropped rather than sent as an invalid type. Its
            // money is still inside the grand total.
            if ($type === null) {
                continue;
            }

            /** @var TotalInterface $total */
            $total = $this->totalInterfaceFactory->create();

            $total->setType($type);
            $total->setDisplayText($this->typeLabel->orFallback((string) $cartTotal->getTitle(), $type));
            $total->setAmount($this->minorUnits->convert((float) $cartTotal->getValue(), $currencyCode));
            $totals[] = $total;
        }

        return $totals;
    }

    /**
     * Magento's own codes are not the spec's vocabulary: `shipping` is `fulfillment` and `grand_total`
     * is `total`.
     *
     * @param string $magentoCode
     * @return string|null
     */
    private function mapType(string $magentoCode): ?string
    {
        return $this->typeMapping[$magentoCode] ?? null;
    }
}
