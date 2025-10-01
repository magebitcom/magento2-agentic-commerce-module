<?php

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SellerName implements OptionSourceInterface
{
    /**
     * @return array<array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => 'Use General -> Store Information -> Name',
                'value' => 'general',
            ],
            [
                'label' => 'Use Custom Value',
                'value' => 'custom',
            ]
        ];
    }
}
