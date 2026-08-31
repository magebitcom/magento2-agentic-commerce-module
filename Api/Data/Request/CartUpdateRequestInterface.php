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

use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\ItemInterface;

/**
 * The body of a cart update. A full replacement: the submitted resource becomes the cart.
 */
interface CartUpdateRequestInterface extends ValidatableRequest
{
    /**
     * @return ItemInterface[]
     */
    public function getLineItems(): array;

    /**
     * @return BuyerInterface|null
     */
    public function getBuyer(): ?BuyerInterface;
}
