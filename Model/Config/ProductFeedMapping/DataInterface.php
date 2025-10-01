<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Config\ProductFeedMapping;

interface DataInterface
{
    /**
     * Get mapping configuration by ID
     *
     * @param string $id
     * @return array|null
     */
    public function getMapping(string $id): ?array;

    /**
     * Get all mapping configurations
     *
     * @return array
     */
    public function getAllMappings(): array;
}
