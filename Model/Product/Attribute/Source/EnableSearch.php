<?php

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Product\Attribute\Source;

class EnableSearch extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public const USE_VISIBILITY = 'visibility';
    public const ENABLED = 'enabled';
    public const DISABLED = 'disabled';

    /**
     * @return array<array{value: string, label: string}>
     */
    public function getAllOptions(): array
    {
        $this->_options = [
            ['value' => self::USE_VISIBILITY, 'label' => __('Use Visibility setting')],
            ['value' => self::ENABLED, 'label' => __('Enabled')],
            ['value' => self::DISABLED, 'label' => __('Disabled')]
        ];
        return $this->_options;
    }
}
