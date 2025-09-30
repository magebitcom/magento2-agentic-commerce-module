<?php

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\ReviewInterface;

trait WithReviewData
{
    /**
     * Get the product review count
     *
     * @return int|null
     */
    public function getProductReviewCount(): ?int
    {
        return $this->getData(ReviewInterface::PRODUCT_REVIEW_COUNT);
    }

    /**
     * Get the product review rating
     *
     * @return float|null
     */
    public function getProductReviewRating(): ?float
    {
        return $this->getData(ReviewInterface::PRODUCT_REVIEW_RATING);
    }

    /**
     * Get the store review count
     *
     * @return int|null
     */
    public function getStoreReviewCount(): ?int
    {
        return $this->getData(ReviewInterface::STORE_REVIEW_COUNT);
    }

    /**
     * Get the store review rating
     *
     * @return float|null
     */
    public function getStoreReviewRating(): ?float
    {
        return $this->getData(ReviewInterface::STORE_REVIEW_RATING);
    }

    /**
     * Get the Q&A
     *
     * @return string|null
     */
    public function getQAndA(): ?string
    {
        return $this->getData(ReviewInterface::Q_AND_A);
    }

    /**
     * Get the raw review data
     *
     * @return string|null
     */
    public function getRawReviewData(): ?string
    {
        return $this->getData(ReviewInterface::RAW_REVIEW_DATA);
    }
}
