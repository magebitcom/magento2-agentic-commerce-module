<?php

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
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
     * @param string $id
     * @return array|null
     */
    public function getMapping(string $id): ?array
    {
        return $this->config->getMapping($id);
    }

    /**
     * Get all mapping configurations
     *
     * @return array
     */
    public function getAllMappings(): array
    {
        return $this->config->getAllMappings();
    }

    /**
     * Get source attribute name from mapping
     *
     * @param string $id
     * @return string|null
     */
    public function getSourceAttribute(string $id): ?string
    {
        $mapping = $this->getMapping($id);
        return $mapping['source_attribute'] ?? null;
    }

    /**
     * Get source object class from mapping
     *
     * @param string $id
     * @return string|null
     */
    public function getSourceObject(string $id): ?string
    {
        $mapping = $this->getMapping($id);
        if (isset($mapping['source']) && is_array($mapping['source'])) {
            return $mapping['source']['value'] ?? null;
        }
        return null;
    }

    /**
     * Get target attribute from mapping
     *
     * @param string $id
     * @return string|null
     */
    public function getTargetAttribute(string $id): ?string
    {
        $mapping = $this->getMapping($id);
        if (isset($mapping['target_attribute']) && is_array($mapping['target_attribute'])) {
            return $mapping['target_attribute']['value'] ?? null;
        }
        return $mapping['target_attribute'] ?? null;
    }

    /**
     * Get formatter class from mapping
     *
     * @param string $id
     * @return string|null
     */
    public function getFormatter(string $id): ?string
    {
        $mapping = $this->getMapping($id);
        if (isset($mapping['formatter']) && is_array($mapping['formatter'])) {
            return $mapping['formatter']['value'] ?? null;
        }
        return null;
    }
}
