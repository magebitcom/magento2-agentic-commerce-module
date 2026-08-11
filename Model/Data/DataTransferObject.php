<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data;

use InvalidArgumentException;
use Magento\Framework\DataObject;

abstract class DataTransferObject extends DataObject
{
    /**
     * @param string[] $keys
     * @return array<mixed>
     */
    public function toArray(array $keys = []): array
    {
        $data = [];

        foreach (parent::toArray($keys) as $key => $value) {
            if ($value instanceof DataObject) {
                $data[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $data[$key] = array_map(function ($item) {
                    if ($item instanceof DataObject) {
                        return $item->toArray();
                    }

                    return $item;
                }, $value);
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * @template T
     * @param string $key
     * @param class-string<T> $interface
     * @param callable(array<mixed>): T $factory
     * @return T|null
     */
    /**
     * An object that was already built, which is what the response DTOs hold.
     *
     * @template T of object
     * @param string $key Field name
     * @param class-string<T> $interface Expected type
     * @return T|null
     */
    protected function getDataOf(string $key, string $interface): ?object
    {
        $value = $this->getData($key);

        return $value instanceof $interface ? $value : null;
    }

    /**
     * A list of objects that were already built, which is what the response DTOs hold.
     *
     * @template T of object
     * @param string $key Field name
     * @param class-string<T> $interface Expected element type
     * @return list<T>
     */
    protected function getDataListOf(string $key, string $interface): array
    {
        $values = [];

        foreach ((array)($this->getData($key) ?? []) as $value) {
            if ($value instanceof $interface) {
                $values[] = $value;
            }
        }

        return $values;
    }

    protected function getDataInstance(string $key, string $interface, callable $factory): mixed
    {
        $data = $this->getData($key);

        if ($data instanceof $interface) {
            return $data;
        }

        if (is_array($data)) {
            return $factory(['data' => $data]);
        }

        return null;
    }

    /**
     * @template T
     * @param string $key
     * @param class-string<T> $interface
     * @param callable(array<mixed>): T $factory
     * @return T[]
     */
    protected function getDataInstanceArray(string $key, string $interface, callable $factory): array
    {
        /** @var array<mixed> $items */
        $items = $this->getData($key) ?? [];

        return array_map(function ($item) use ($factory, $interface) {
            if ($item instanceof $interface) {
                return $item;
            }

            /** @var array<mixed> $item */
            return $factory(['data' => $item]);
        }, $items);
    }

    /**
     * @param string $key
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getDataString(string $key): string
    {
        $data = $this->getData($key);

        if (!is_string($data)) {
            throw new \InvalidArgumentException(sprintf('Data for key %s is not a string', $key));
        }

        return $data;
    }

    /**
     * @param string $key
     * @return null|string
     */
    protected function getDataStringOrNull(string $key): ?string
    {
        $data = $this->getData($key);
        return is_string($data) ? $data : null;
    }

    /**
     * @param string $key
     * @return int
     * @throws InvalidArgumentException
     */
    protected function getDataInt(string $key): int
    {
        // @phpstan-ignore cast.int
        return (int) $this->getData($key);
    }

    /**
     * Get raw data array for validation
     *
     * @return array<string, mixed>
     */
    public function getRawData(): array
    {
        return $this->_data;
    }
}
