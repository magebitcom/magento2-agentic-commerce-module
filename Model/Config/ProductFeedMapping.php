<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Config;

use Magebit\AgenticCommerce\Model\Config\ProductFeedMapping\DataInterface;

class ProductFeedMapping
{
    /**
     * @param DataInterface $config
     */
    public function __construct(
        private readonly DataInterface $config
    ) {
    }

    /**
     * Get mapping configuration by ID
     *
     * @param string $type
     * @return array|null
     */
    public function getMappingsForType(string $type): ?array
    {
        return $this->config->getMappingsForType($type);
    }

    /**
     * Get mapping configuration by types
     *
     * @param array $types
     * @return array
     */
    public function getMappingsForTypes(array $types): array
    {
        $mappings = [];

        foreach ($types as $type) {
            $mappings = array_merge($mappings, $this->getMappingsForType($type));
        }

        return $mappings;
    }
}
