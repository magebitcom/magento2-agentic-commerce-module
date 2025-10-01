<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Mapping;

use Magebit\AgenticCommerce\Api\Data\FeedProductInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magebit\AgenticCommerce\Api\ProductMapperInterface;

class MapperComposite implements ProductMapperInterface
{
    /**
     * @param ProductMapperInterface[] $productTypeMappers
     */
    public function __construct(
        private readonly array $productTypeMappers = []
    ) {
    }

    /**
     * Maps Magento product to FeedProductInterface
     * Can return multiple FeedProductInterfaces if product has multiple variants
     *
     * @param ProductInterface $product
     * @return FeedProductInterface[]
     */
    public function map(ProductInterface $product): array
    {
        return $this->getProductTypeMapper($product)->map($product);
    }

    /**
     * @param ProductInterface $product
     * @return ProductMapperInterface
     */
    public function getProductTypeMapper(ProductInterface $product): ProductMapperInterface
    {
        $type = $product->getTypeId();

        if (!isset($this->productTypeMappers[$type])) {
            if (isset($this->productTypeMappers['simple'])) {
                return $this->productTypeMappers['simple'];
            }

            throw new \InvalidArgumentException('Product type mapper not found for type: ' . $type);
        }

        return $this->productTypeMappers[$type];
    }
}
