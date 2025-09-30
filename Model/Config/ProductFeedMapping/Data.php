<?php

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Config\ProductFeedMapping;

use Magento\Framework\Config\Data as ConfigData;

class Data extends ConfigData implements DataInterface
{
    /**
     * Get mapping configuration by ID
     *
     * @param string $id
     * @return array|null
     */
    public function getMapping(string $id): ?array
    {
        return $this->get($id);
    }

    /**
     * Get all mapping configurations
     *
     * @return array
     */
    public function getAllMappings(): array
    {
        return $this->get();
    }
}
