<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Mapping\Source;

use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;

class SalePriceEffectiveDate implements SourceInterface
{
    /**
     * @param UrlBuilder $imageUrlBuilder
     */
    public function __construct(
        private readonly UrlBuilder $imageUrlBuilder
    ) {
    }

    /**
     * @param ProductInterface $product
     * @param ProductInterface|null $parentProduct
     * @return mixed
     */
    public function getValue(ProductInterface $product, ?ProductInterface $parentProduct = null): mixed
    {
        /** @var Product $product */
        if (!$product->getSpecialFromDate() || !$product->getSpecialToDate() || !$product->getSpecialPrice()) {
            return null;
        }

        $fromDate = date('Y-m-d', strtotime($product->getSpecialFromDate()));
        $toDate = date('Y-m-d', strtotime($product->getSpecialToDate()));

        return $fromDate . ' / ' . $toDate;
    }
}
