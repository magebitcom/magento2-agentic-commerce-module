<?php

namespace Magebit\AgenticCommerce\Model\Mapping\Source;

use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;

class Image implements SourceInterface
{
    /**
     * @param UrlBuilder $imageUrlBuilder
     */
    public function __construct(
        private readonly UrlBuilder $imageUrlBuilder
    ) {
    }

    /**
     * @param ProductInterface $product
     * @return mixed
     */
    public function getValue(ProductInterface $product): mixed
    {
        /** @var Product $product */
        $image = $product->getImage();

        if (!$image) {
            return null;
        }

        return $this->imageUrlBuilder->getUrl($image, 'product_page_image_large');
    }
}
