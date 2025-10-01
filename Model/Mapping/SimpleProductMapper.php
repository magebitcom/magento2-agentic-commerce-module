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

class SimpleProductMapper extends AbstractMapper
{
    /**
     * @param ProductInterface $product
     * @return FeedProductInterface[]
     */
    public function map(ProductInterface $product): array
    {
        $data = [];

        $allMappings = $this->productFeedMapping->getAllMappings();

        foreach ($allMappings as $mapping) {
            $data[$mapping[self::CONFIG_KEY_TARGET_ATTRIBUTE]] = $this->mapAttribute($product, $mapping);
        }

        return [$this->feedProductFactory->create(['data' => $data])];
    }
}
