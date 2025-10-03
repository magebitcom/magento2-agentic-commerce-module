<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Config\ProductFeedMapping;

interface DataInterface
{
    /**
     * Get mapping configuration by type
     *
     * @param string $type
     * @return array|null
     */
    public function getMappingsForType(string $type): ?array;
}
