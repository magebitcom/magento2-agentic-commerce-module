<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

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
