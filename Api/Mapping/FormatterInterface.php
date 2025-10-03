<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

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
