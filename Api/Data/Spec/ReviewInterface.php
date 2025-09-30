<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Api\Data\Spec;

interface ReviewInterface
{
    public const PRODUCT_REVIEW_COUNT = 'product_review_count';
    public const PRODUCT_REVIEW_RATING = 'product_review_rating';
    public const STORE_REVIEW_COUNT = 'store_review_count';
    public const STORE_REVIEW_RATING = 'store_review_rating';
    public const Q_AND_A = 'q_and_a';
    public const RAW_REVIEW_DATA = 'raw_review_data';

    /**
     * Get the product review count
     *
     * @return int|null
     */
    public function getProductReviewCount(): ?int;

    /**
     * Get the product review rating
     *
     * @return float|null
     */
    public function getProductReviewRating(): ?float;

    /**
     * Get the store review count
     *
     * @return int|null
     */
    public function getStoreReviewCount(): ?int;

    /**
     * Get the store review rating
     *
     * @return float|null
     */
    public function getStoreReviewRating(): ?float;

    /**
     * Get the Q&A
     *
     * @return string|null
     */
    public function getQAndA(): ?string;

    /**
     * Get the raw review data
     *
     * @return string|null
     */
    public function getRawReviewData(): ?string;
}

