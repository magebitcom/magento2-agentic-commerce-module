<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AcpSpec\Api\Cart\CartInterface;
use Magebit\AcpSpec\Api\Cart\CartInterfaceFactory;
use Magento\Quote\Model\Quote;

/**
 * Builds the cart resource. `Cart` is `additionalProperties: false` and carries no status, payment,
 * fulfillment or capabilities, so it is built from its own fields rather than trimmed from a session.
 */
class CartToAcpCart
{
    /**
     * @param CartInterfaceFactory $cartFactory
     * @param CartItemToLineItem $cartItemToLineItem
     * @param CartToTotals $cartToTotals
     * @param CartToBuyer $cartToBuyer
     */
    public function __construct(
        private readonly CartInterfaceFactory $cartFactory,
        private readonly CartItemToLineItem $cartItemToLineItem,
        private readonly CartToTotals $cartToTotals,
        private readonly CartToBuyer $cartToBuyer
    ) {
    }

    /**
     * @param Quote $quote
     * @param string $cartId
     * @return CartInterface
     */
    public function execute(Quote $quote, string $cartId): CartInterface
    {
        $lineItems = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            $lineItems[] = $this->cartItemToLineItem->execute($item);
        }

        /** @var CartInterface $cart */
        $cart = $this->cartFactory->create();
        $cart->setId($cartId);
        $cart->setLineItems($lineItems);
        $cart->setCurrency($this->currencyOf($quote));
        $cart->setTotals($this->cartToTotals->execute($quote));

        $buyer = $this->cartToBuyer->execute($quote);

        if ($buyer !== null) {
            $cart->setBuyer($buyer);
        }

        return $cart;
    }

    /**
     * @param Quote $quote
     * @return string
     */
    private function currencyOf(Quote $quote): string
    {
        return strtoupper((string) ($quote->getCurrency()?->getStoreCurrencyCode() ?? 'USD'));
    }
}
