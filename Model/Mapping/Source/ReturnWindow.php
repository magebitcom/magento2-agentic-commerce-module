<?php

namespace Magebit\AgenticCommerce\Model\Mapping\Source;

use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magebit\AgenticCommerce\Api\ConfigInterface;

class ReturnWindow implements SourceInterface
{
    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * @param ProductInterface $product
     * @return mixed
     */
    public function getValue(ProductInterface $product): mixed
    {
        /** @var Product $product */
        return $this->config->getReturnWindow((int) $product->getStoreId());
    }
}
