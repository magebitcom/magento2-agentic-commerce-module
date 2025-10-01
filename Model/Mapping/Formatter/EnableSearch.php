<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Mapping\Formatter;

use Magebit\AgenticCommerce\Api\Mapping\FormatterInterface;
use Magebit\AgenticCommerce\Enum\ProductAttribute;
use Magebit\AgenticCommerce\Model\Product\Attribute\Source\EnableSearch as EnableSearchSource;
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
        /** @var Product $product */
        $enableSearch = $product->getData(ProductAttribute::ENABLE_SEARCH->value) ?? EnableSearchSource::USE_VISIBILITY;

        if ($enableSearch === EnableSearchSource::USE_VISIBILITY) {
            return in_array($value, [Visibility::VISIBILITY_IN_SEARCH, Visibility::VISIBILITY_BOTH]) ? 'true' : 'false';
        }

        return $enableSearch === EnableSearchSource::ENABLED ? 'true' : 'false';
    }
}
