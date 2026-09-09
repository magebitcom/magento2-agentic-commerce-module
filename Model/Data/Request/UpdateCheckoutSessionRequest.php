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

use Magebit\AcpSpec\Data\AgenticCheckout\CheckoutSessionUpdateRequest as SpecUpdateRequest;
use Magebit\AgenticCommerce\Api\Data\ItemInterface;
use Magebit\AgenticCommerce\Api\Data\Request\UpdateCheckoutSessionRequestInterface;

/**
 * The generated update request, reading its items back as the ones this module accepts.
 */
class UpdateCheckoutSessionRequest extends SpecUpdateRequest implements UpdateCheckoutSessionRequestInterface
{
    /**
     * @inheritDoc
     */
    public function getLineItems(): ?array
    {
        return $this->instanceListOrNull(self::KEY_LINE_ITEMS, ItemInterface::class);
    }
}
