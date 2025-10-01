<?php

namespace Magebit\AgenticCommerce\Enum;

enum ProductAttribute: string
{
    case ENABLE_CHECKOUT = 'ac_enable_checkout';
    case ENABLE_SEARCH = 'ac_enable_search';
    case MPN_SOURCE = 'ac_mpn_source';
}
