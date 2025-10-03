<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class LinkTypeColumn extends Select
{
    /**
     * @param string $value
     * @return self
     */
    public function setInputName(string $value): self
    {
        return $this->setName($value);
    }

    /**
     * @param string $value
     * @return self
     */
    public function setInputId(string $value): self
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }
    /**
     * GetSourceOptions function
     *
     * @return array<int, array{label: string, value: int}>
     */
    private function getSourceOptions(): array
    {
        return [
            ['label' => 'Terms of Use', 'value' => 'terms_of_use'],
            ['label' => 'Privacy Policy', 'value' => 'privacy_policy'],
            ['label' => 'Seller Shop Policies', 'value' => 'seller_shop_policies'],
        ];
    }
}
