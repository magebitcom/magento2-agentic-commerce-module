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

interface ProductInterface
{
    public const ID = 'id';
    public const GTIN = 'gtin';
    public const MPN = 'mpn';
    public const TITLE = 'title';
    public const DESCRIPTION = 'description';
    public const LINK = 'link';

    /**
     * Get the ID
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Get the GTIN
     *
     * @return string|null
     */
    public function getGtin(): ?string;

    /**
     * Get the MPN
     *
     * @return string|null
     */
    public function getMpn(): ?string;

    /**
     * Get the title
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get the link
     *
     * @return string
     */
    public function getLink(): string;
}
