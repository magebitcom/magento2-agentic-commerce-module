<?php

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Config\ProductFeedMapping;

use DOMElement;
use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    /**
     * Convert DOM document to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source): array
    {
        $mappings = $source->getElementsByTagName('mapping');
        $data = [];

        foreach ($mappings as $mapping) {
            $data[$mapping->getAttribute('id')] = $this->processMapping($mapping);
        }

        return $data;
    }

    /**
     * Process mapping
     *
     * @param DOMElement $mapping
     * @return array<mixed>
     */
    protected function processMapping(DOMElement $mapping): array
    {
        $data = [];

        foreach ($mapping->childNodes as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $field = [
                'value' => $node->nodeValue,
                'xsi:type' => $node->getAttribute('xsi:type')
            ];

            $data[$node->nodeName] = $field;
        }

        return $data;
    }
}
