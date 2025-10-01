<?php

namespace Magebit\AgenticCommerce\Model\Mapping\Source;

use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

class FinalPrice implements SourceInterface
{
    /**
     * @param ProductInterface $product
     * @return mixed
     */
    public function getValue(ProductInterface $product): mixed
    {
        /** @var Product $product */
        $productPrice = $product->getPrice();
        $finalPrice = $product->getFinalPrice();

        if ($finalPrice < $productPrice) {
            return $finalPrice;
        }

        return null;
    }
}
