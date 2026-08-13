<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AgenticCore\Model\Money\MinorUnits;
use Magebit\AgenticCore\Model\Total\TypeLabel;
use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\ItemInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\ItemInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\LineItemInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\LineItemInterfaceFactory;
use Magento\Quote\Model\Quote\Item;

class CartItemToLineItem
{
    /**
     * @param LineItemInterfaceFactory $lineItemFactory
     * @param ItemInterfaceFactory $itemFactory
     * @param TotalInterfaceFactory $totalFactory
     * @param MinorUnits $minorUnits
     * @param TypeLabel $typeLabel
     */
    public function __construct(
        protected readonly LineItemInterfaceFactory $lineItemFactory,
        protected readonly ItemInterfaceFactory $itemFactory,
        protected readonly TotalInterfaceFactory $totalFactory,
        protected readonly MinorUnits $minorUnits,
        protected readonly TypeLabel $typeLabel,
    ) {
    }

    /**
     * @param Item $cartItem
     * @return LineItemInterface
     */
    public function execute(Item $cartItem): LineItemInterface
    {
        $quantity = (int)$cartItem->getQty();

        // Quantity lives on the line item now, so the spec's own Item is enough here.
        /** @var ItemInterface $item */
        $item = $this->itemFactory->create();
        $item->setId((string)$cartItem->getProduct()->getSku());
        $item->setName((string)$cartItem->getName());

        /** @var LineItemInterface $lineItem */
        $lineItem = $this->lineItemFactory->create();
        $lineItem->setId((string)$cartItem->getItemId());
        $lineItem->setItem($item);
        // Required on LineItem by the spec, even though `Item` itself does not declare it.
        $lineItem->setQuantity($quantity);
        $lineItem->setTotals($this->buildTotals($cartItem));

        return $lineItem;
    }

    /**
     * The spec keeps line-item money in a typed breakdown rather than flat fields, and only the
     * amounts that apply are emitted.
     *
     * @param Item $cartItem
     * @return TotalInterface[]
     */
    private function buildTotals(Item $cartItem): array
    {
        $currencyCode = $cartItem->getQuote()->getCurrency()?->getStoreCurrencyCode() ?? 'USD';

        $baseAmount = (float)$cartItem->getPrice() * (float)$cartItem->getQty();
        // Magento reports the discount as a positive magnitude to subtract.
        $discount = abs((float)$cartItem->getDiscountAmount());

        $amounts = [
            TotalInterface::TYPE_ITEMS_BASE_AMOUNT => $baseAmount,
            TotalInterface::TYPE_ITEMS_DISCOUNT => $discount,
            TotalInterface::TYPE_SUBTOTAL => $baseAmount - $discount,
            TotalInterface::TYPE_TAX => (float)$cartItem->getTaxAmount(),
            TotalInterface::TYPE_TOTAL => (float)$cartItem->getRowTotalInclTax(),
        ];

        $totals = [];

        foreach ($amounts as $type => $amount) {
            // A zero discount or tax is noise; the base amount, subtotal and total always apply.
            if ($amount === 0.0 && in_array($type, [
                TotalInterface::TYPE_ITEMS_DISCOUNT,
                TotalInterface::TYPE_TAX,
            ], true)) {
                continue;
            }

            /** @var TotalInterface $total */
            $total = $this->totalFactory->create();
            $total->setType($type);
            // Required by the schema, and these totals are derived rather than titled by Magento.
            $total->setDisplayText($this->typeLabel->for($type));
            $total->setAmount($this->minorUnits->convert($amount, $currencyCode));

            $totals[] = $total;
        }

        return $totals;
    }
}
