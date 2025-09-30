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
use Magento\Framework\Config\Dom\ArrayNodeConfig;
use Magento\Framework\Config\Dom\NodePathMatcher;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\ObjectManager\Config\Mapper\ArgumentParser;
use Magento\Framework\Config\Converter\Dom\Flat as FlatConverter;

class Converter implements ConverterInterface
{
    /**
     * @var FlatConverter|null
     */
    private ?FlatConverter $converter = null;

    /**
     * @param InterpreterInterface $argumentInterpreter
     * @param ArgumentParser $argumentParser
     */
    public function __construct(
        private readonly InterpreterInterface $argumentInterpreter,
        private readonly ArgumentParser $argumentParser
    ) {
    }

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
     * @return array{source_attribute: string|null, target_attribute: string|null, formatter: string|null, source: string|null}
     */
    protected function processMapping(DOMElement $mapping): array
    {
        $data = [];

        foreach ($mapping->childNodes as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            if ($node->getAttribute('xsi:type')) {
                $data[$node->nodeName] = $this->argumentInterpreter->evaluate([
                    'xsi:type' => $node->getAttribute('xsi:type'),
                    'value' => $node->nodeValue,
                ]);
            } else {
                $data[$node->nodeName] = $node->nodeValue;
            }
        }

        return $data;
    }
}
