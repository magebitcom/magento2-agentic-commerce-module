<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api;

use Magebit\AcpSpec\Api\Cart\CartInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CartCreateRequestInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CartUpdateRequestInterface;

/**
 * The cart capability's own operations. A cart is basket building before purchase intent: no payment,
 * no status lifecycle and no completion.
 */
interface CartServiceInterface
{
    /**
     * @param CartCreateRequestInterface $request
     * @return CartInterface
     */
    public function create(CartCreateRequestInterface $request): CartInterface;

    /**
     * @param string $cartId
     * @return CartInterface
     */
    public function retrieve(string $cartId): CartInterface;

    /**
     * A full replacement: the platform sends the entire cart resource.
     *
     * @param string $cartId
     * @param CartUpdateRequestInterface $request
     * @return CartInterface
     */
    public function update(string $cartId, CartUpdateRequestInterface $request): CartInterface;

    /**
     * Returns the cart state before deletion; later reads report it gone.
     *
     * @param string $cartId
     * @return CartInterface
     */
    public function cancel(string $cartId): CartInterface;
}
