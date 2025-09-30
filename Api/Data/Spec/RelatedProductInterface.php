<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Api\Data\Spec;

interface RelatedProductInterface
{
    public const RELATED_PRODUCT_ID = 'related_product_id';
    public const RELATIONSHIP_TYPE = 'relationship_type';

    /**
     * Get the related product ID
     *
     * @return string|null
     */
    public function getRelatedProductId(): ?string;

    /**
     * Get the relationship type
     *
     * @return string|null
     */
    public function getRelationshipType(): ?string;
}

