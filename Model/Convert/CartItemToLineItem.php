<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AgenticCommerce\Api\Data\LineItemInterface;
use Magebit\AgenticCommerce\Api\Data\LineItemInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\ItemInterface;
use Magebit\AgenticCommerce\Api\Data\ItemInterfaceFactory;
use Magento\Quote\Model\Quote\Item;

class CartItemToLineItem
{
    public function __construct(
        protected readonly LineItemInterfaceFactory $lineItemFactory,
        protected readonly ItemInterfaceFactory $itemFactory,
        protected readonly ConvertPrice $convertPrice,
    ) {
    }

    /**
     * @param Item $cartItem
     * @return LineItemInterface
     */
    public function execute(Item $cartItem): LineItemInterface
    {
        /** @var LineItemInterface $lineItem */
        $lineItem = $this->lineItemFactory->create();
        $lineItem->setId((string) $cartItem->getItemId());

        /** @var ItemInterface $item */
        $item = $this->itemFactory->create();
        $item->setId((string) $cartItem->getProduct()->getSku());
        $item->setQuantity($cartItem->getQty());

        $baseAmount = $cartItem->getPrice() * $cartItem->getQty();
        $discount = $cartItem->getDiscountAmount();
        $subtotal = $baseAmount - $discount;
        $total = $cartItem->getRowTotalInclTax();
        $tax = $cartItem->getTaxAmount();

        $lineItem->setBaseAmount($this->convertPrice->execute($baseAmount));
        $lineItem->setSubtotal($this->convertPrice->execute($subtotal));
        $lineItem->setDiscount($this->convertPrice->execute($discount));
        $lineItem->setTotal($this->convertPrice->execute($total));
        $lineItem->setTax($this->convertPrice->execute($tax));
        $lineItem->setItem($item);

        return $lineItem;
    }
}
