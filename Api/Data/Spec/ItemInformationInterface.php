<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

namespace Magebit\AgenticCommerce\Api\Data\Spec;

interface ItemInformationInterface
{
    public const CONDITION = 'condition';
    public const PRODUCT_CATEGORY = 'product_category';
    public const BRAND = 'brand';
    public const MATERIAL = 'material';
    public const DIMENSIONS = 'dimensions';
    public const LENGTH = 'length';
    public const WIDTH = 'width';
    public const HEIGHT = 'height';
    public const WEIGHT = 'weight';
    public const AGE_GROUP = 'age_group';

    public const CONDITION_NEW = 'new';
    public const CONDITION_USED = 'used';
    public const CONDITION_REFURBISHED = 'refurbished';

    /**
     * Get the condition
     *
     * @return string
     */
    public function getCondition(): string;

    /**
     * Get the product category
     *
     * @return string
     */
    public function getProductCategory(): string;

    /**
     * Get the brand
     *
     * @return string|null
     */
    public function getBrand(): ?string;

    /**
     * Get the material
     *
     * @return string
     */
    public function getMaterial(): string;

    /**
     * Get the dimensions
     *
     * @return string|null
     */
    public function getDimensions(): ?string;

    /**
     * Get the length
     *
     * @return string|null
     */
    public function getLength(): ?string;

    /**
     * Get the width
     *
     * @return string|null
     */
    public function getWidth(): ?string;

    /**
     * Get the height
     *
     * @return string|null
     */
    public function getHeight(): ?string;

    /**
     * Get the weight
     *
     * @return string
     */
    public function getWeight(): string;

    /**
     * Get the age group
     *
     * @return string|null
     */
    public function getAgeGroup(): ?string;
}
