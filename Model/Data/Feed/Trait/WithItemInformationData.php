<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Feed\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\ItemInformationInterface;

trait WithItemInformationData
{
    /**
     * Get the condition
     *
     * @return string
     */
    public function getCondition(): string
    {
        return $this->getData(ItemInformationInterface::CONDITION);
    }

    /**
     * Get the product category
     *
     * @return string
     */
    public function getProductCategory(): string
    {
        return $this->getData(ItemInformationInterface::PRODUCT_CATEGORY);
    }

    /**
     * Get the brand
     *
     * @return string|null
     */
    public function getBrand(): ?string
    {
        return $this->getData(ItemInformationInterface::BRAND);
    }

    /**
     * Get the material
     *
     * @return string
     */
    public function getMaterial(): string
    {
        return $this->getData(ItemInformationInterface::MATERIAL);
    }

    /**
     * Get the dimensions
     *
     * @return string|null
     */
    public function getDimensions(): ?string
    {
        return $this->getData(ItemInformationInterface::DIMENSIONS);
    }

    /**
     * Get the length
     *
     * @return string|null
     */
    public function getLength(): ?string
    {
        return $this->getData(ItemInformationInterface::LENGTH);
    }

    /**
     * Get the width
     *
     * @return string|null
     */
    public function getWidth(): ?string
    {
        return $this->getData(ItemInformationInterface::WIDTH);
    }

    /**
     * Get the height
     *
     * @return string|null
     */
    public function getHeight(): ?string
    {
        return $this->getData(ItemInformationInterface::HEIGHT);
    }

    /**
     * Get the weight
     *
     * @return string
     */
    public function getWeight(): string
    {
        return $this->getData(ItemInformationInterface::WEIGHT);
    }

    /**
     * Get the age group
     *
     * @return string|null
     */
    public function getAgeGroup(): ?string
    {
        return $this->getData(ItemInformationInterface::AGE_GROUP);
    }
}
