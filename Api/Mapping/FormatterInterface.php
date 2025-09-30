<?php

namespace Magebit\AgenticCommerce\Api\Mapping;

use Magento\Catalog\Api\Data\ProductInterface;

interface FormatterInterface
{
    /**
     * @param ProductInterface $product
     * @param mixed $value
     * @return mixed
     */
    public function format(ProductInterface $product, $value): mixed;
}
