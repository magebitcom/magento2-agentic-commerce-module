<?php

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
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
     * @var array<mixed>|null
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
    public function getMapping(string $id): ?array
    {
        return $this->interpretMapping($this->get($id));
    }

    /**
     * Get all mapping configurations
     *
     * @return array
     */
    public function getAllMappings(): array
    {
        if ($this->mappings) {
            return $this->mappings;
        }
        $mappings = $this->get();
        $this->mappings = array_map([$this, 'interpretMapping'], $mappings);
        return $this->mappings;
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
