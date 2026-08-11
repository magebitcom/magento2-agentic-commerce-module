<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Block\Adminhtml\Form\Field;

use Magebit\AcpSpec\Api\AgenticCheckout\LinkInterface;
use Magento\Framework\View\Element\Html\Select;

class LinkTypeColumn extends Select
{
    /**
     * The spec's complete `Link.type` enum; `seller_shop_policies` is not one of them.
     */
    private const TYPES = [
        LinkInterface::TYPE_TERMS_OF_USE,
        LinkInterface::TYPE_PRIVACY_POLICY,
        LinkInterface::TYPE_RETURN_POLICY,
        LinkInterface::TYPE_SHIPPING_POLICY,
        LinkInterface::TYPE_CONTACT_US,
        LinkInterface::TYPE_ABOUT_US,
        LinkInterface::TYPE_FAQ,
        LinkInterface::TYPE_SUPPORT,
    ];

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
     * Driven off the generated constants so the options cannot drift from the spec's enum.
     *
     * @return array<int, array{label: string, value: string}>
     */
    private function getSourceOptions(): array
    {
        $options = [];

        foreach (self::TYPES as $value) {
            $options[] = [
                'label' => ucwords(str_replace('_', ' ', $value)),
                'value' => $value,
            ];
        }

        return $options;
    }
}
