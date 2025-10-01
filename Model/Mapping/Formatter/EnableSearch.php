<?php

namespace Magebit\AgenticCommerce\Model\Mapping\Formatter;

use Magebit\AgenticCommerce\Api\Mapping\FormatterInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;

class EnableSearch implements FormatterInterface
{
    /**
     * @param ProductInterface $product
     * @param mixed $value
     * @return mixed
     */
    public function format(ProductInterface $product, $value): mixed
    {
        return in_array($value, [Visibility::VISIBILITY_IN_SEARCH, Visibility::VISIBILITY_BOTH]);
    }
}
