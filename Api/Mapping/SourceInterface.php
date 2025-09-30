<?php

namespace Magebit\AgenticCommerce\Api\Mapping;

use Magento\Catalog\Api\Data\ProductInterface;

interface SourceInterface
{
    /**
     * @param ProductInterface $product
     * @return mixed
     */
    public function getValue(ProductInterface $product): mixed;
}
