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

use Magebit\AcpSpec\Data\AgenticCheckout\CheckoutSessionCompleteRequest as SpecCompleteRequest;
use Magebit\AgenticCommerce\Api\Data\Request\CompleteCheckoutSessionRequestInterface;

/**
 * The generated complete request under the name this module's services take.
 */
class CompleteCheckoutSessionRequest extends SpecCompleteRequest implements CompleteCheckoutSessionRequestInterface
{
}
