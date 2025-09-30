<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

namespace Magebit\AgenticCommerce\Api\Data;

interface FeedProductInterface extends
    Spec\ProductInterface,
    Spec\MediaInterface,
    Spec\ItemInformationInterface,
    Spec\VariantInterface,
    Spec\AvailabilityInterface,
    Spec\FulfillmentInterface,
    Spec\PriceInterface,
    Spec\GeoTaggingInterface,
    Spec\RelatedProductInterface
{
    //
}
