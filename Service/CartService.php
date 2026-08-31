<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Service;

use Magebit\AcpSpec\Api\Cart\CartInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CartCreateRequestInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CartUpdateRequestInterface;
use Magebit\AgenticCommerce\Api\CartServiceInterface;
use Magebit\AgenticCommerce\Model\Convert\CartToAcpCart;
use Magebit\AcpSpec\Api\AgenticCheckout\ItemInterface;
use Magebit\AcpSpec\Runtime\SpecObject;
use Magebit\AgenticCommerce\Exception\CartNotFoundException;
use Magebit\AgenticCommerce\Model\Quote\BuyerWriter;
use Magebit\AgenticCore\Model\Quote\LineItemWriter;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * Serves the cart capability on the same guest quote a checkout session uses, minus everything a cart
 * does not have: no payment, no status lifecycle, no completion.
 */
class CartService implements CartServiceInterface
{
    /**
     * @param GuestCartManagementInterface $guestCartManagement
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param CartRepositoryInterface $cartRepository
     * @param LineItemWriter $lineItemWriter
     * @param BuyerWriter $buyerWriter
     * @param CartToAcpCart $cartConverter
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly GuestCartManagementInterface $guestCartManagement,
        private readonly GuestCartRepositoryInterface $guestCartRepository,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly LineItemWriter $lineItemWriter,
        private readonly BuyerWriter $buyerWriter,
        private readonly CartToAcpCart $cartConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function create(CartCreateRequestInterface $request): CartInterface
    {
        $cartId = $this->guestCartManagement->createEmptyCart();
        $quote = $this->quoteFor($cartId);

        $this->writeItems($quote, $request->getLineItems());
        $this->writeBuyer($quote, $request);
        $this->save($quote);

        $this->logger->info('Cart created', ['cart_id' => $cartId]);

        return $this->cartConverter->execute($quote, $cartId);
    }

    /**
     * @inheritDoc
     */
    public function retrieve(string $cartId): CartInterface
    {
        $quote = $this->liveQuoteFor($cartId);

        // Totals are not collected on a freshly loaded quote, and the converter reports them.
        $quote->collectTotals();

        return $this->cartConverter->execute($quote, $cartId);
    }

    /**
     * @inheritDoc
     */
    public function update(string $cartId, CartUpdateRequestInterface $request): CartInterface
    {
        $quote = $this->liveQuoteFor($cartId);

        // A full replacement, which the spec is explicit about: the submitted resource becomes the
        // cart. The shared writer clears the quote itself, so nothing is removed here first.
        $this->writeItems($quote, $request->getLineItems());
        $this->writeBuyer($quote, $request);
        $this->save($quote);

        $this->logger->info('Cart updated', ['cart_id' => $cartId]);

        return $this->cartConverter->execute($quote, $cartId);
    }

    /**
     * @inheritDoc
     */
    public function cancel(string $cartId): CartInterface
    {
        $quote = $this->liveQuoteFor($cartId);
        $quote->collectTotals();
        $cart = $this->cartConverter->execute($quote, $cartId);

        // Built before deactivating, because the spec has cancel return the state before deletion.
        $quote->setIsActive(false);
        $this->cartRepository->save($quote);

        $this->logger->info('Cart canceled', ['cart_id' => $cartId]);

        return $cart;
    }

    /**
     * @param Quote $quote
     * @param array<int, ItemInterface>|null $items
     * @return void
     */
    private function writeItems(Quote $quote, ?array $items): void
    {
        $this->lineItemWriter->write($quote, array_map(
            static fn (ItemInterface $item): array => [
                'sku' => (string) $item->getId(),
                'quantity' => self::quantityOf($item),
            ],
            array_values($items ?? [])
        ));
    }

    /**
     * Read through the runtime rather than a typed getter: `Item` is `additionalProperties: false` with
     * no `quantity`, yet the spec's own request examples send one. Accepted leniently, defaulting to a
     * single unit, until that is resolved upstream.
     *
     * @param ItemInterface $item
     * @return int
     */
    private static function quantityOf(ItemInterface $item): int
    {
        $quantity = $item instanceof SpecObject ? $item->get('quantity') : null;

        return is_numeric($quantity) && (int) $quantity > 0 ? (int) $quantity : 1;
    }

    /**
     * @param Quote $quote
     * @param CartCreateRequestInterface|CartUpdateRequestInterface $request
     * @return void
     */
    private function writeBuyer(Quote $quote, CartCreateRequestInterface|CartUpdateRequestInterface $request): void
    {
        $buyer = $request->getBuyer();

        if ($buyer === null) {
            return;
        }

        $this->buyerWriter->write($quote, $buyer);
    }

    /**
     * @param string $cartId
     * @return Quote
     */
    private function quoteFor(string $cartId): Quote
    {
        /** @var Quote $quote */
        $quote = $this->guestCartRepository->get($cartId);

        return $quote;
    }

    /**
     * A canceled cart is gone rather than a cart in a canceled state, so reads report it missing.
     *
     * @param string $cartId
     * @return Quote
     * @throws CartNotFoundException
     */
    private function liveQuoteFor(string $cartId): Quote
    {
        $quote = $this->quoteFor($cartId);

        if (!$quote->getIsActive()) {
            throw new CartNotFoundException($cartId);
        }

        return $quote;
    }

    /**
     * @param Quote $quote
     * @return void
     */
    private function save(Quote $quote): void
    {
        $quote->collectTotals();
        $this->cartRepository->save($quote);
    }
}
