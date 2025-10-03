<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Config\Source;

use Magebit\AgenticCommerce\Enum\ProductAttribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Data\OptionSourceInterface;

class GtinSource implements OptionSourceInterface
{
    /**
     * @var array<array{value: string, label: string}>
     */
    private array $options = [];

    private const EXCLUDED_ATTRIBUTES = [
        ProductAttribute::ENABLE_CHECKOUT->value,
        ProductAttribute::ENABLE_SEARCH->value,
    ];

    /**
     * @param CollectionFactory $collectionFactory
     * @return void
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory
    ) {
    }

    /**
     * @return array<array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        if (!empty($this->options)) {
            return $this->options;
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect([
            AttributeInterface::ATTRIBUTE_CODE,
            AttributeInterface::FRONTEND_LABEL,
        ]);

        $this->options = [];

        foreach ($collection as $attribute) {
            if (in_array($attribute->getData(AttributeInterface::ATTRIBUTE_CODE), self::EXCLUDED_ATTRIBUTES)) {
                continue;
            }

            $this->options[] = [
                'value' => $attribute->getData(AttributeInterface::ATTRIBUTE_CODE),
                'label' => $attribute->getData(AttributeInterface::FRONTEND_LABEL),
            ];
        }

        return $this->options;
    }
}
