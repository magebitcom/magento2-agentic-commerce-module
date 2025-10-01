<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api;

interface ProductFeedWriterInterface
{
    /**
     * @param FeedProductInterface[] $products
     * @param int $page
     * @return void
     */
    public function write(array $products, int $page): void;

    /**
     * @param string $feedFilePath
     * @return void
     */
    public function setFeedFilePath(string $feedFilePath): void;
}
