<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Mapping\Source;

use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

class ItemGroupId implements SourceInterface
{
    /**
     * @param ProductInterface $product
     * @param ProductInterface|null $parentProduct
     * @return mixed
     */
    public function getValue(ProductInterface $product, ?ProductInterface $parentProduct = null): mixed
    {
        /** @var Product $product */
        return $parentProduct?->getSku();
    }
}
