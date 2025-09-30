<?php

namespace Magebit\AgenticCommerce\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magebit\AgenticCommerce\Api\Data\FeedProductInterface;

interface ProductMapperInterface
{
    /**
     * @param ProductInterface $product
     * @return FeedProductInterface
     */
    public function map(ProductInterface $product): FeedProductInterface;
}
