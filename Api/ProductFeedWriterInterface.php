<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
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
     * Called once after the last page, so a writer that has to see every product before it can lay
     * the file out has somewhere to do it.
     *
     * @return void
     */
    public function finish(): void;

    /**
     * @param string $feedFilePath
     * @return void
     */
    public function setFeedFilePath(string $feedFilePath): void;
}
