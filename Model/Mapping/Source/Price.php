<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Mapping\Source;

use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

/**
 * What the product costs.
 *
 * A bundle or a grouped product holds no price of its own, so reading the price field gave zero for
 * a bundle and nothing for a grouped product. The feed then offered a paid bundle for nothing.
 */
class Price implements SourceInterface
{
    /**
     * @param ProductInterface $product
     * @param ProductInterface|null $parentProduct
     * @return float|null
     */
    public function getValue(ProductInterface $product, ?ProductInterface $parentProduct = null): ?float
    {
        /** @var Product $product */
        $own = $this->toPrice($product->getData('price'));

        if ($own !== null && $own > 0.0) {
            return $own;
        }

        // The cheapest way to buy it, which is what the price index holds for these types.
        return $this->toPrice($product->getData('min_price'));
    }

    /**
     * @param mixed $value
     * @return float|null
     */
    private function toPrice(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
