<?php

namespace Magebit\AgenticCommerce\Api;

use Magento\Catalog\Api\Data\ProductInterface;

interface ProductMappingDataProviderInterface
{
    /**
     * @param ProductInterface $product
     * @return array<string, mixed>
     */
    public function getData(ProductInterface $product): array;
}
