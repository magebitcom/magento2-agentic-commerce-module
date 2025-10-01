<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\RelatedProductInterface;

trait WithRelatedProductData
{
    /**
     * Get the related product ID
     *
     * @return string|null
     */
    public function getRelatedProductId(): ?string
    {
        return $this->getData(RelatedProductInterface::RELATED_PRODUCT_ID);
    }

    /**
     * Get the relationship type
     *
     * @return string|null
     */
    public function getRelationshipType(): ?string
    {
        return $this->getData(RelatedProductInterface::RELATIONSHIP_TYPE);
    }
}
