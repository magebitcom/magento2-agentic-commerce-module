<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Exception;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

/**
 * A cart that never existed or was canceled. Its status is binary, so a canceled cart is reported gone
 * rather than as a cart in a canceled state.
 */
class CartNotFoundException extends NoSuchEntityException
{
    /**
     * @param string $cartId
     */
    public function __construct(string $cartId)
    {
        parent::__construct(new Phrase('Cart not found: %1.', [$cartId]));
    }
}
