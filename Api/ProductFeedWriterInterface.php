<?php

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
