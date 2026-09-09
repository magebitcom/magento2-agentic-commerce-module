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

use Magebit\AcpSpec\Api\AgenticCheckout\CheckoutSessionCompleteRequestInterface;

/**
 * The specification's complete request. Nothing is narrowed: the generated interface already says
 * everything this module needs, and it is named here so the module owns the type its services take.
 */
interface CompleteCheckoutSessionRequestInterface extends CheckoutSessionCompleteRequestInterface
{
}
