<?php

namespace Magebit\AgenticCommerce\Model\Mapping\Source;

use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Enum\ProductAttribute;

class SellerTos implements SourceInterface
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
        if (!!$product->getData(ProductAttribute::ENABLE_CHECKOUT->value)) {
            return null;
        }

        /** @var Product $product */
        return $this->config->getSellerTosUrl((int) $product->getStoreId());
    }
}
