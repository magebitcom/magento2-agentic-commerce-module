<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Stock;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Whether a SKU can be bought. Asked of the stock registry rather than the product, because a product
 * loaded through a collection carries no stock data and reports itself salable regardless.
 */
class Availability
{
    /**
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        private readonly StockRegistryInterface $stockRegistry
    ) {
    }

    /**
     * @param string $sku
     * @return bool
     */
    public function isSalable(string $sku): bool
    {
        try {
            return (bool) $this->stockRegistry->getProductStockStatusBySku($sku);
        } catch (NoSuchEntityException $exception) {
            return false;
        }
    }
}
