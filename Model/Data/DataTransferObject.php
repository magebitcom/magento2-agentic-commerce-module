<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data;

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
}
