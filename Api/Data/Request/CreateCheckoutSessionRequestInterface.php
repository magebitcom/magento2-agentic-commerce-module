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

use Magebit\AcpSpec\Api\AgenticCheckout\CheckoutSessionCreateRequestInterface;

/**
 * The specification's create request, with the line items narrowed to the ones this module accepts.
 */
interface CreateCheckoutSessionRequestInterface extends CheckoutSessionCreateRequestInterface
{
    /**
     * Narrowed so each item carries the `quantity` every upstream example sends; the specification's
     * own `Item` does not declare it. See the defect recorded in the acp-php-spec README.
     *
     * @return \Magebit\AgenticCommerce\Api\Data\ItemInterface[]
     */
    public function getLineItems(): array;
}
