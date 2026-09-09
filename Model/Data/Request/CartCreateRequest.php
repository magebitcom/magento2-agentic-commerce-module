<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Request;

use Magebit\AcpSpec\Data\Cart\CartCreateRequest as SpecCartCreateRequest;
use Magebit\AgenticCommerce\Api\Data\ItemInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CartCreateRequestInterface;

/**
 * The generated cart create request, reading its items back as the ones this module accepts.
 */
class CartCreateRequest extends SpecCartCreateRequest implements CartCreateRequestInterface
{
    /**
     * @inheritDoc
     */
    public function getLineItems(): array
    {
        return $this->instanceList(self::KEY_LINE_ITEMS, ItemInterface::class);
    }
}
