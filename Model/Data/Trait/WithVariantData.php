<?php

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\VariantInterface;

trait WithVariantData
{
    /**
     * Get the item group ID
     *
     * @return string|null
     */
    public function getItemGroupId(): ?string
    {
        return $this->getData(VariantInterface::ITEM_GROUP_ID);
    }

    /**
     * Get the item group title
     *
     * @return string|null
     */
    public function getItemGroupTitle(): ?string
    {
        return $this->getData(VariantInterface::ITEM_GROUP_TITLE);
    }

    /**
     * Get the color
     *
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->getData(VariantInterface::COLOR);
    }

    /**
     * Get the size
     *
     * @return string|null
     */
    public function getSize(): ?string
    {
        return $this->getData(VariantInterface::SIZE);
    }

    /**
     * Get the size system
     *
     * @return string|null
     */
    public function getSizeSystem(): ?string
    {
        return $this->getData(VariantInterface::SIZE_SYSTEM);
    }

    /**
     * Get the gender
     *
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->getData(VariantInterface::GENDER);
    }

    /**
     * Get the offer ID
     *
     * @return string|null
     */
    public function getOfferId(): ?string
    {
        return $this->getData(VariantInterface::OFFER_ID);
    }

    /**
     * Get the custom variant 1 category
     *
     * @return string|null
     */
    public function getCustomVariant1Category(): ?string
    {
        return $this->getData(VariantInterface::CUSTOM_VARIANT1_CATEGORY);
    }

    /**
     * Get the custom variant 1 option
     *
     * @return string|null
     */
    public function getCustomVariant1Option(): ?string
    {
        return $this->getData(VariantInterface::CUSTOM_VARIANT1_OPTION);
    }

    /**
     * Get the custom variant 2 category
     *
     * @return string|null
     */
    public function getCustomVariant2Category(): ?string
    {
        return $this->getData(VariantInterface::CUSTOM_VARIANT2_CATEGORY);
    }

    /**
     * Get the custom variant 2 option
     *
     * @return string|null
     */
    public function getCustomVariant2Option(): ?string
    {
        return $this->getData(VariantInterface::CUSTOM_VARIANT2_OPTION);
    }

    /**
     * Get the custom variant 3 category
     *
     * @return string|null
     */
    public function getCustomVariant3Category(): ?string
    {
        return $this->getData(VariantInterface::CUSTOM_VARIANT3_CATEGORY);
    }

    /**
     * Get the custom variant 3 option
     *
     * @return string|null
     */
    public function getCustomVariant3Option(): ?string
    {
        return $this->getData(VariantInterface::CUSTOM_VARIANT3_OPTION);
    }
}
