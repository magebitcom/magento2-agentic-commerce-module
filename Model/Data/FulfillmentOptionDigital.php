<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data;

use Magebit\AgenticCommerce\Api\Data\FulfillmentOptionDigitalInterface;

/**
 * Fulfillment Option Digital Data Transfer Object
 */
class FulfillmentOptionDigital extends FulfillmentOption implements FulfillmentOptionDigitalInterface
{
    // Digital fulfillment options only require the base implementation
}
