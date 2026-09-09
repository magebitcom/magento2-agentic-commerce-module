<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api;

use Magebit\AcpSpec\Api\Feed\FeedMetadataInterface;
use Magebit\AcpSpec\Api\Feed\ProductsResponseInterface;

/**
 * The ACP feed surface. A feed here is the store's own catalogue for one target country, so there is
 * nothing to store: the id encodes the country and the products come from the catalogue.
 */
interface FeedServiceInterface
{
    /**
     * @param string|null $targetCountry Two-letter code; the store's own country when omitted
     * @return FeedMetadataInterface
     */
    public function create(?string $targetCountry): FeedMetadataInterface;

    /**
     * @param string $feedId
     * @return FeedMetadataInterface
     */
    public function metadata(string $feedId): FeedMetadataInterface;

    /**
     * @param string $feedId
     * @param int|null $limit
     * @param int $offset
     * @return ProductsResponseInterface
     */
    public function products(string $feedId, ?int $limit = null, int $offset = 0): ProductsResponseInterface;
}
