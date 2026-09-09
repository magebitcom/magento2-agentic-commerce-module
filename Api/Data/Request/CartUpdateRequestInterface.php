<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data\Request;

use Magebit\AcpSpec\Api\Cart\CartUpdateRequestInterface as SpecCartUpdateRequestInterface;

/**
 * The body of a cart update. A full replacement: the submitted resource becomes the cart.
 */
interface CartUpdateRequestInterface extends SpecCartUpdateRequestInterface
{
    /**
     * Narrowed the same way the checkout session requests narrow it, so a submitted quantity is kept
     * rather than dropped: the specification's own `Item` declares no quantity to keep.
     *
     * @return \Magebit\AgenticCommerce\Api\Data\ItemInterface[]
     */
    public function getLineItems(): array;
}
