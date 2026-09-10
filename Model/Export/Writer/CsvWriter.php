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

use Magebit\AgenticCommerce\Api\Data\FeedProductInterface;
use Magebit\AgenticCommerce\Api\ProductFeedWriterInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Writes the feed as one CSV file.
 *
 * Products do not all carry the same columns: a variant of a configurable product adds a pair for
 * every option it varies by. So the rows are buffered while the full set of columns is learned, and
 * the file is written once at the end with a header that covers all of them. Writing rows straight
 * out meant the header described only whichever product came first, and every row after it with a
 * different set of columns was written shifted against that header.
 */
class CsvWriter implements ProductFeedWriterInterface
{
    /**
     * Appended to the feed's own path for the file rows are buffered into.
     */
    private const BUFFER_SUFFIX = '.rows';

    /**
     * Longest buffered row to expect, in bytes. A product description is the only large field.
     */
    private const MAX_ROW_LENGTH = 1048576;

    /**
     * Every column seen so far, in the order it first appeared.
     *
     * @var array<string, true>
     */
    private array $columns = [];

    /**
     * How many rows were buffered, so the read back asks for exactly that many. Probing for the end
     * of the file is not reliable: the reader reports it only once a read has already failed.
     */
    private int $rows = 0;

    /**
     * @param Filesystem $filesystem
     * @param Json $serializer
     * @param string $feedFilePath
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly Json $serializer,
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
        if ($page === 1) {
            $this->columns = [];
            $this->rows = 0;
        }

        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $stream = $directory->openFile($this->bufferPath(), $page === 1 ? 'w' : 'a');
        $stream->lock();

        foreach ($products as $product) {
            $row = $product->toArray();

            foreach (array_keys($row) as $column) {
                $this->columns[(string) $column] = true;
            }

            $stream->write($this->serializer->serialize($row) . "\n");
            $this->rows++;
        }

        $stream->unlock();
        $stream->close();
    }

    /**
     * Turns the buffered rows into the feed file. Every row is written against the same header, so a
     * product missing a column gets an empty value rather than shifting the row.
     *
     * @return void
     */
    public function finish(): void
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $columns = array_keys($this->columns);

        $feed = $directory->openFile($this->feedFilePath, 'w');
        $feed->lock();
        $feed->writeCsv($columns);

        if ($this->rows > 0 && $directory->isExist($this->bufferPath())) {
            $buffer = $directory->openFile($this->bufferPath(), 'r');

            for ($row = 0; $row < $this->rows; $row++) {
                $line = trim((string) $buffer->readLine(self::MAX_ROW_LENGTH, "\n"));

                if ($line === '') {
                    continue;
                }

                $feed->writeCsv($this->align($line, $columns));
            }

            $buffer->close();
            $directory->delete($this->bufferPath());
        }

        $feed->unlock();
        $feed->close();

        $this->columns = [];
        $this->rows = 0;
    }

    /**
     * @param string $line One buffered row
     * @param string[] $columns The header, in order
     * @return array<int, string>
     */
    private function align(string $line, array $columns): array
    {
        $row = $this->serializer->unserialize($line);

        if (!is_array($row)) {
            $row = [];
        }

        $aligned = [];

        foreach ($columns as $column) {
            $aligned[] = $this->toCell($row[$column] ?? null);
        }

        return $aligned;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function toCell(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_string($value)) {
            return $value;
        }

        // Anything left that a cell cannot hold, such as a list, is written as an empty cell.
        return is_int($value) || is_float($value) ? (string) $value : '';
    }

    /**
     * @return string
     */
    private function bufferPath(): string
    {
        return $this->feedFilePath . self::BUFFER_SUFFIX;
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
