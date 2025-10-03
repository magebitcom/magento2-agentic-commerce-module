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
use Magebit\AgenticCommerce\Api\Data\FeedProductInterface;

interface ProductMapperInterface
{
    /**
     * @param ProductInterface $product
     * @return FeedProductInterface[]
     */
    public function map(ProductInterface $product): array;
}
