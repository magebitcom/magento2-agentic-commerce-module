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

use Magebit\AgenticCommerce\Api\Data\ValidatableDataInterface;

/**
 * A request body the controller both builds and validates.
 */
interface ValidatableRequest extends RequestInterface, ValidatableDataInterface
{
}
