<?php

namespace Magebit\AgenticCommerce\Model\Mapping\Source;

use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magebit\AgenticCommerce\Enum\ProductAttribute;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

class EnableCheckout implements SourceInterface
{
    /**
     * @param ProductInterface $product
     * @return mixed
     */
    public function getValue(ProductInterface $product): mixed
    {
        /** @var Product $product */
        return !!$product->getData(ProductAttribute::ENABLE_CHECKOUT->value);
    }
}
