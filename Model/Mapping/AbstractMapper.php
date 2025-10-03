<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Mapping;

use Magebit\AgenticCommerce\Api\ProductMapperInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magebit\AgenticCommerce\Api\Data\FeedProductInterfaceFactory;
use Magebit\AgenticCommerce\Api\Mapping\FormatterInterface;
use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magebit\AgenticCommerce\Model\Config\ProductFeedMapping;
use Magento\Catalog\Model\Product;

abstract class AbstractMapper implements ProductMapperInterface
{
    protected const CONFIG_KEY_SOURCE_ATTRIBUTE = 'source_attribute';
    protected const CONFIG_KEY_TARGET_ATTRIBUTE = 'target_attribute';
    protected const CONFIG_KEY_FORMATTER = 'formatter';

    /**
     * @param FeedProductInterfaceFactory $feedProductFactory
     * @param ProductFeedMapping $productFeedMapping
     * @param ProductMapperInterface[] $productTypeMappers
     */
    public function __construct(
        protected readonly FeedProductInterfaceFactory $feedProductFactory,
        protected readonly ProductFeedMapping $productFeedMapping
    ) {
    }

    /**
     * Map product attribute to value
     *
     * @param ProductInterface $product
     * @param array $mapping
     * @param ProductInterface|null $parentProduct
     * @return mixed
     */
    public function mapAttribute(ProductInterface $product, array $mapping, ?ProductInterface $parentProduct = null): mixed
    {
        /** @var Product $product */
        $value = $this->getAttributeValue($product, $mapping, $parentProduct);
        $value = $this->formatValue($value, $product, $mapping);

        return $value;
    }

    /**
     * Format value using formatter
     *
     * @param mixed $value
     * @param ProductInterface $product
     * @param array $mapping
     * @param ProductInterface|null $parentProduct
     * @return mixed
     */
    public function formatValue(mixed $value, ProductInterface $product, array $mapping, ?ProductInterface $parentProduct = null): mixed
    {
        $value = $this->getAttributeValue($product, $mapping);

        if (isset($mapping[self::CONFIG_KEY_FORMATTER])) {
            $formatter = $mapping[self::CONFIG_KEY_FORMATTER];

            if (!($formatter instanceof FormatterInterface)) {
                throw new \InvalidArgumentException('Formatter must implement FormatterInterface');
            }

            $value = $formatter->format($product, $value);
        }

        return $value;
    }

    /**
     * Get attribute value from product
     *
     * @param ProductInterface $product
     * @param array $mapping
     * @param ProductInterface|null $parentProduct
     * @return mixed
     */
    public function getAttributeValue(ProductInterface $product, array $mapping, ?ProductInterface $parentProduct = null): mixed
    {
        if (!isset($mapping[self::CONFIG_KEY_SOURCE_ATTRIBUTE])) {
            throw new \InvalidArgumentException('Source attribute is required');
        }

        $sourceAttribute = $mapping[self::CONFIG_KEY_SOURCE_ATTRIBUTE];

        if ($sourceAttribute instanceof SourceInterface) {
            return $sourceAttribute->getValue($product, $parentProduct);
        }

        if (!is_string($sourceAttribute)) {
            throw new \InvalidArgumentException('Source attribute must be a string or implement SourceInterface');
        }

        /** @var Product $product */
        return $product->getData($sourceAttribute);
    }
}
