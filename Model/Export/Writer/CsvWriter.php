<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Export\Writer;

use Magebit\AgenticCommerce\Api\ProductFeedWriterInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class CsvWriter implements ProductFeedWriterInterface
{
    /**
     * @param Filesystem $filesystem
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        private string $feedFilePath = 'export/agentic_commerce.csv'
    ) {
    }

    /**
     * @param FeedProductInterface[] $products
     * @param int $page
     * @return void
     */
    public function write(array $products, int $page): void
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $stream = $directory->openFile($this->feedFilePath, $page === 1 ? 'w' : 'a');
        $stream->lock();

        $didWriteHeader = false;

        foreach ($products as $product) {
            if ($page === 1 && !$didWriteHeader) {
                $stream->writeCsv(array_keys($product->getData()));
                $didWriteHeader = true;
            }

            $stream->writeCsv($product->getData());
        }

        $stream->unlock();
        $stream->close();
    }

    /**
     * @param string $feedFilePath
     * @return void
     */
    public function setFeedFilePath(string $feedFilePath): void
    {
        $this->feedFilePath = $feedFilePath;
    }
}
