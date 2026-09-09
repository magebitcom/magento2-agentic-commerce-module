<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

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
    /**
     * The product as one row of column names and values, which is what a feed file is written from.
     *
     * @param string[] $keys Columns to return, or none for all of them
     * @return array<string, mixed>
     */
    public function toArray(array $keys = []): array;
}
