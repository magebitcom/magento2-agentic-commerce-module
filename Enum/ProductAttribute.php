<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Enum;

enum ProductAttribute: string
{
    case ENABLE_CHECKOUT = 'ac_enable_checkout';
    case ENABLE_SEARCH = 'ac_enable_search';
    case MPN_SOURCE = 'ac_mpn_source';
}
