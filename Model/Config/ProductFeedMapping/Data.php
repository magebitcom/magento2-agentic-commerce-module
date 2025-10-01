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

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data as ConfigData;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Data extends ConfigData implements DataInterface
{
    /**
     * @var array<string, array<mixed>>|null
     */
    private ?array $mappings = null;

    public function __construct(
        private readonly InterpreterInterface $argumentInterpreter,
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId,
        ?SerializerInterface $serializer = null,
        ?array $cacheTags = null,
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer, $cacheTags);
    }

    /**
     * Get mapping configuration by ID
     *
     * @param string $id
     * @return array|null
     */
    public function getMappingsForType(string $type): ?array
    {
        if (isset($this->mappings[$type])) {
            return $this->mappings[$type];
        }
        $mappings = $this->get($type) ?? [];
        $this->mappings[$type] = array_map([$this, 'interpretMapping'], $mappings);
        return $this->mappings[$type];
    }

    /**
     * Interpret mapping
     *
     * @param array<mixed> $mapping
     * @return array<mixed>
     */
    private function interpretMapping(array $mapping): array
    {
        $data = [];
        foreach ($mapping as $key => $value) {
            if ($value['xsi:type']) {
                $data[$key] = $this->argumentInterpreter->evaluate($value);
            } else {
                $data[$key] = $value['value'];
            }
        }
        return $data;
    }
}
