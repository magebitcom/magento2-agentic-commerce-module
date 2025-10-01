<?php

namespace Magebit\AgenticCommerce\Model\Mapping;

use Magebit\AgenticCommerce\Api\Data\FeedProductInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magebit\AgenticCommerce\Api\Data\FeedProductInterfaceFactory;
use Magebit\AgenticCommerce\Api\Mapping\FormatterInterface;
use Magebit\AgenticCommerce\Api\Mapping\SourceInterface;
use Magebit\AgenticCommerce\Model\Config\ProductFeedMapping;
use Magebit\AgenticCommerce\Api\ProductMapperInterface;

class ProductMapper implements ProductMapperInterface
{
    private const CONFIG_KEY_SOURCE_ATTRIBUTE = 'source_attribute';
    private const CONFIG_KEY_TARGET_ATTRIBUTE = 'target_attribute';
    private const CONFIG_KEY_FORMATTER = 'formatter';

    /**
     * @param FeedProductInterfaceFactory $feedProductFactory
     * @param ProductFeedMapping $productFeedMapping
     */
    public function __construct(
        private readonly FeedProductInterfaceFactory $feedProductFactory,
        private readonly ProductFeedMapping $productFeedMapping,
    ) {
    }

    /**
     * @param ProductInterface $product
     * @return FeedProductInterface
     */
    public function map(ProductInterface $product): FeedProductInterface
    {
        $data = [];

        $allMappings = $this->productFeedMapping->getAllMappings();

        foreach ($allMappings as $mapping) {
            $data[$mapping[self::CONFIG_KEY_TARGET_ATTRIBUTE]] = $this->mapAttribute($product, $mapping);
        }

        return $this->feedProductFactory->create(['data' => $data]);
    }

    /**
     * @param ProductInterface $product
     * @param array $mapping
     * @return mixed
     */
    public function mapAttribute(ProductInterface $product, array $mapping): mixed
    {
        /** @var Product $product */
        $value = $this->getAttributeValue($product, $mapping);
        $value = $this->formatValue($value, $product, $mapping);

        return $value;
    }

    /**
     * @param mixed $value
     * @param ProductInterface $product
     * @param array $mapping
     * @return mixed
     */
    public function formatValue(mixed $value, ProductInterface $product, array $mapping): mixed
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
     * @param ProductInterface $product
     * @param array $mapping
     * @return mixed
     */
    public function getAttributeValue(ProductInterface $product, array $mapping): mixed
    {
        if (!isset($mapping[self::CONFIG_KEY_SOURCE_ATTRIBUTE])) {
            throw new \InvalidArgumentException('Source attribute is required');
        }

        $sourceAttribute = $mapping[self::CONFIG_KEY_SOURCE_ATTRIBUTE];

        if ($sourceAttribute instanceof SourceInterface) {
            return $sourceAttribute->getValue($product);
        }

        if (!is_string($sourceAttribute)) {
            throw new \InvalidArgumentException('Source attribute must be a string or implement SourceInterface');
        }

        /** @var Product $product */
        return $product->getData($sourceAttribute);
    }
}
