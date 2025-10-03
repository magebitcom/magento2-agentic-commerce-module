<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Mapping\Formatter;

use Magebit\AgenticCommerce\Api\Mapping\FormatterInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Price implements FormatterInterface
{
    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Format price ISO 4217
     *
     * @param ProductInterface $product
     * @param mixed $value
     * @return mixed
     */
    public function format(ProductInterface $product, $value): mixed
    {
        if (!$value) {
            return null;
        }

        /** @var Product $product */
        /** @var Store $store */
        $store = $this->storeManager->getStore($product->getStoreId());
        $currency = $store->getCurrentCurrencyCode();

        return number_format((float) $value, 2, '.', '') . ' ' . $currency;
    }
}
