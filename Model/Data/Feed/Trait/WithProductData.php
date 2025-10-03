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

use Magebit\AgenticCommerce\Api\Data\Spec\ProductInterface;

trait WithProductData
{
    /**
     * Get the ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->getData(ProductInterface::ID);
    }

    /**
     * Get the GTIN
     *
     * @return string|null
     */
    public function getGtin(): ?string
    {
        return $this->getData(ProductInterface::GTIN);
    }

    /**
     * Get the MPN
     *
     * @return string|null
     */
    public function getMpn(): ?string
    {
        return $this->getData(ProductInterface::MPN);
    }

    /**
     * Get the title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getData(ProductInterface::TITLE);
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getData(ProductInterface::DESCRIPTION);
    }

    /**
     * Get the link
     *
     * @return string
     */
    public function getLink(): string
    {
        return $this->getData(ProductInterface::LINK);
    }
}
