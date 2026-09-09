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

use Magebit\AcpSpec\Api\AgenticCheckout\CheckoutSessionUpdateRequestInterface;

/**
 * The specification's update request, with the line items narrowed to the ones this module accepts.
 */
interface UpdateCheckoutSessionRequestInterface extends CheckoutSessionUpdateRequestInterface
{
    /**
     * Narrowed the same way the create request narrows it. Still nullable, because an update that
     * names no items leaves the ones already there alone, and nullability is what says "optional".
     *
     * @return \Magebit\AgenticCommerce\Api\Data\ItemInterface[]|null
     */
    public function getLineItems(): ?array;
}
