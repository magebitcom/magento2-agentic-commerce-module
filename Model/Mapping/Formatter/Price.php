<?php

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
        /** @var Store $store */
        $store = $this->storeManager->getStore($product->getStoreId());
        $currency = $store->getCurrentCurrencyCode();

        return number_format($value, 2, '.', '') . ' ' . $currency;
    }
}
