<?php

namespace Magebit\AgenticCommerce\Model\Mapping;

use Magebit\AgenticCommerce\Api\Data\FeedProductInterface;
use Magento\Catalog\Api\Data\ProductInterface;
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
    private const CONFIG_KEY_SOURCE = 'source';

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
        if (isset($mapping[self::CONFIG_KEY_SOURCE_ATTRIBUTE])) {
            $sourceAttribute = $mapping[self::CONFIG_KEY_SOURCE_ATTRIBUTE];
            $value = $product->getData($sourceAttribute);
        } elseif (isset($mapping[self::CONFIG_KEY_SOURCE])) {
            $source = $mapping[self::CONFIG_KEY_SOURCE];

            if (!($source instanceof SourceInterface)) {
                throw new \InvalidArgumentException('Source must implement SourceInterface');
            }

            $value = $source->getValue($product);
        } else {
            throw new \InvalidArgumentException('Source attribute or source object is required');
        }

        if (isset($mapping[self::CONFIG_KEY_FORMATTER])) {
            $formatter = $mapping[self::CONFIG_KEY_FORMATTER];

            if (!($formatter instanceof FormatterInterface)) {
                throw new \InvalidArgumentException('Formatter must implement FormatterInterface');
            }

            $value = $formatter->format($product, $value);
        }

        return $value;
    }
}
