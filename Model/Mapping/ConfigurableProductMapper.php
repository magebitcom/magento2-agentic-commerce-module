<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Mapping;

use InvalidArgumentException;
use LogicException;
use Exception;
use Magebit\AgenticCommerce\Api\Data\FeedProductInterface;
use Magebit\AgenticCommerce\Api\ProductMapperInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

class ConfigurableProductMapper extends AbstractMapper implements ProductMapperInterface
{
    /**
     * @param ProductInterface $product
     * @return FeedProductInterface[]
     */
    public function map(ProductInterface $product): array
    {
        $childProducts = $this->getChildProducts($product);

        $feedProducts = [];

        foreach ($childProducts as $childProduct) {
            $feedProducts[] = $this->mapChildProduct($childProduct, $product);
        }

        return $feedProducts;
    }

    /**
     * Map child product
     *
     * @param ProductInterface $product
     * @param ProductInterface $parentProduct
     * @return FeedProductInterface
     */
    public function mapChildProduct(ProductInterface $product, ProductInterface $parentProduct): FeedProductInterface
    {
        $allMappings = $this->productFeedMapping->getMappingsForTypes(['all', 'configurable']);

        foreach ($allMappings as $mapping) {
            $data[$mapping[self::CONFIG_KEY_TARGET_ATTRIBUTE]] = $this->mapAttribute($product, $mapping, $parentProduct);
            $data = array_merge($data, $this->addVariantAttributes($product, $parentProduct));
        }

        return $this->feedProductFactory->create(['data' => $data]);
    }

    /**
     * @param ProductInterface $product
     * @param ProductInterface $parentProduct
     * @return void
     */
    public function addVariantAttributes(ProductInterface $product, ProductInterface $parentProduct)
    {
        $data = [];

        $i = 1;
        foreach ($this->getConfigurableAttributes($parentProduct) as $attribute) {
            $eavAttribute = $attribute->getProductAttribute();
            $value = $product->getData($eavAttribute->getAttributeCode());

            $data['Custom_variant' . $i . '_category'] = $eavAttribute->getFrontend()->getLabel();
            $data['Custom_variant' . $i . '_option'] = $eavAttribute->getSource()->getOptionText($value);
            $i++;
        }

        return $data;
    }

    /**
     * @param ProductInterface $product
     * @return Configurable\Attribute[]
     */
    public function getConfigurableAttributes(ProductInterface $product)
    {
        /** @var Product $product */
        /** @var Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();

        return $typeInstance->getConfigurableAttributes($product);
    }

    /**
     * Get child products of configurable product
     *
     * @param ProductInterface $product
     * @return Product[]
     */
    public function getChildProducts(ProductInterface $product): array
    {
        /** @var Product $product */
        /** @var Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        return $typeInstance->getUsedProducts($product);
    }
}
