<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data\Spec;

interface VariantInterface
{
    public const ITEM_GROUP_ID = 'item_group_id';
    public const ITEM_GROUP_TITLE = 'item_group_title';
    public const COLOR = 'color';
    public const SIZE = 'size';
    public const SIZE_SYSTEM = 'size_system';
    public const GENDER = 'gender';
    public const OFFER_ID = 'offer_id';
    public const CUSTOM_VARIANT1_CATEGORY = 'custom_variant1_category';
    public const CUSTOM_VARIANT1_OPTION = 'custom_variant1_option';
    public const CUSTOM_VARIANT2_CATEGORY = 'custom_variant2_category';
    public const CUSTOM_VARIANT2_OPTION = 'custom_variant2_option';
    public const CUSTOM_VARIANT3_CATEGORY = 'custom_variant3_category';
    public const CUSTOM_VARIANT3_OPTION = 'custom_variant3_option';

    /**
     * Get the item group ID
     *
     * @return string|null
     */
    public function getItemGroupId(): ?string;

    /**
     * Get the item group title
     *
     * @return string|null
     */
    public function getItemGroupTitle(): ?string;

    /**
     * Get the color
     *
     * @return string|null
     */
    public function getColor(): ?string;

    /**
     * Get the size
     *
     * @return string|null
     */
    public function getSize(): ?string;

    /**
     * Get the size system
     *
     * @return string|null
     */
    public function getSizeSystem(): ?string;

    /**
     * Get the gender
     *
     * @return string|null
     */
    public function getGender(): ?string;

    /**
     * Get the offer ID
     *
     * @return string|null
     */
    public function getOfferId(): ?string;

    /**
     * Get the custom variant 1 category
     *
     * @return string|null
     */
    public function getCustomVariant1Category(): ?string;

    /**
     * Get the custom variant 1 option
     *
     * @return string|null
     */
    public function getCustomVariant1Option(): ?string;

    /**
     * Get the custom variant 2 category
     *
     * @return string|null
     */
    public function getCustomVariant2Category(): ?string;

    /**
     * Get the custom variant 2 option
     *
     * @return string|null
     */
    public function getCustomVariant2Option(): ?string;

    /**
     * Get the custom variant 3 category
     *
     * @return string|null
     */
    public function getCustomVariant3Category(): ?string;

    /**
     * Get the custom variant 3 option
     *
     * @return string|null
     */
    public function getCustomVariant3Option(): ?string;
}
