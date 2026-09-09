<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\Export\Writer;

use Magebit\AgenticCommerce\Api\Data\FeedProductInterface;
use Magebit\AgenticCommerce\Model\Data\Feed\Product;
use Magebit\AgenticCommerce\Model\Export\Writer\CsvWriter;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

/**
 * Products do not all carry the same columns, because a variant adds a pair for every option it
 * varies by. The header used to be taken from whichever product came first, so every row with a
 * different set of columns was written shifted against it and read back as another product's data.
 */
class CsvWriterTest extends TestCase
{
    private const FEED_PATH = 'export/agentic_commerce.csv';

    /**
     * Lines written to the buffer file.
     *
     * @var string[]
     */
    private array $buffered = [];

    /**
     * Rows written to the feed file, the header first.
     *
     * @var array<int, array<int, string>>
     */
    private array $written = [];

    private int $readAt = 0;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->buffered = [];
        $this->written = [];
        $this->readAt = 0;
    }

    /**
     * @return void
     */
    public function testTheHeaderCoversEveryColumnAnyProductCarries(): void
    {
        $this->export([
            ['id' => 'simple-1', 'price' => '10.00 USD'],
            ['id' => 'variant-1', 'price' => '12.00 USD', 'Custom_variant1_option' => 'XS'],
        ]);

        $this->assertSame(['id', 'price', 'Custom_variant1_option'], $this->written[0]);
    }

    /**
     * @return void
     */
    public function testEveryRowHasAsManyCellsAsTheHeader(): void
    {
        $this->export([
            ['id' => 'simple-1', 'price' => '10.00 USD'],
            ['id' => 'variant-1', 'price' => '12.00 USD', 'Custom_variant1_option' => 'XS'],
            ['id' => 'variant-2', 'price' => '13.00 USD', 'Custom_variant1_option' => 'S'],
        ]);

        $width = count($this->written[0]);

        foreach ($this->written as $index => $row) {
            $this->assertCount($width, $row, sprintf('row %d has the wrong number of cells', $index));
        }
    }

    /**
     * The failure this guards against: a product with fewer columns used to shift the ones after it,
     * so a price could be read as an option and an option as a policy link.
     *
     * @return void
     */
    public function testAProductMissingAColumnGetsAnEmptyCellRatherThanAShiftedRow(): void
    {
        $this->export([
            ['id' => 'variant-1', 'price' => '12.00 USD', 'Custom_variant1_option' => 'XS'],
            ['id' => 'simple-1', 'price' => '10.00 USD'],
        ]);

        $this->assertSame(['id', 'price', 'Custom_variant1_option'], $this->written[0]);
        $this->assertSame(['variant-1', '12.00 USD', 'XS'], $this->written[1]);
        $this->assertSame(['simple-1', '10.00 USD', ''], $this->written[2]);
    }

    /**
     * A column first seen on a later product still has to appear in the header, and the rows before
     * it get an empty cell there.
     *
     * @return void
     */
    public function testAColumnFirstSeenLateStillReachesTheHeader(): void
    {
        $this->export([
            ['id' => 'simple-1'],
            ['id' => 'variant-1', 'item_group_id' => 'MH01'],
        ]);

        $this->assertSame(['id', 'item_group_id'], $this->written[0]);
        $this->assertSame(['simple-1', ''], $this->written[1]);
        $this->assertSame(['variant-1', 'MH01'], $this->written[2]);
    }

    /**
     * @return void
     */
    public function testAnUnsetValueIsWrittenAsAnEmptyCell(): void
    {
        $this->export([['id' => 'simple-1', 'seller_name' => null]]);

        $this->assertSame(['simple-1', ''], $this->written[1]);
    }

    /**
     * @return void
     */
    public function testABooleanIsWrittenAsAWord(): void
    {
        $this->export([['id' => 'simple-1', 'enable_search' => true]]);

        $this->assertSame(['simple-1', 'true'], $this->written[1]);
    }

    /**
     * The pages are separate calls, and the header cannot be written until the last one has been
     * seen, so a column that only the second page carries still has to reach it.
     *
     * @return void
     */
    public function testColumnsAreGatheredAcrossPages(): void
    {
        $writer = $this->writer();
        $writer->write([$this->product(['id' => 'simple-1'])], 1);
        $writer->write([$this->product(['id' => 'variant-1', 'item_group_id' => 'MH01'])], 2);
        $writer->finish();

        $this->assertSame(['id', 'item_group_id'], $this->written[0]);
        $this->assertSame(['simple-1', ''], $this->written[1]);
        $this->assertSame(['variant-1', 'MH01'], $this->written[2]);
    }

    /**
     * A second export must not inherit the first one's columns.
     *
     * @return void
     */
    public function testASecondExportStartsFromAnEmptyHeader(): void
    {
        $writer = $this->writer();
        $writer->write([$this->product(['id' => 'a', 'item_group_id' => 'MH01'])], 1);
        $writer->finish();

        $this->written = [];
        $this->buffered = [];
        $this->readAt = 0;

        $writer->write([$this->product(['id' => 'b'])], 1);
        $writer->finish();

        $this->assertSame(['id'], $this->written[0]);
    }

    /**
     * @param array<int, array<string, mixed>> $products Data for each product, in feed order
     * @return void
     */
    private function export(array $products): void
    {
        $writer = $this->writer();
        $writer->write(array_map(fn (array $data): FeedProductInterface => $this->product($data), $products), 1);
        $writer->finish();
    }

    /**
     * @return CsvWriter
     */
    private function writer(): CsvWriter
    {
        $writer = new CsvWriter($this->filesystem(), new Json());
        $writer->setFeedFilePath(self::FEED_PATH);

        return $writer;
    }

    /**
     * @param array<string, mixed> $data
     * @return FeedProductInterface
     */
    private function product(array $data): FeedProductInterface
    {
        return new Product($data);
    }

    /**
     * A filesystem held in memory: the buffer file collects the serialised rows and hands them back
     * a line at a time, and the feed file collects the CSV rows for the assertions.
     *
     * @return Filesystem
     */
    private function filesystem(): Filesystem
    {
        $buffer = $this->createMock(FileWriteInterface::class);
        $buffer->method('write')->willReturnCallback(
            function (string $line): int {
                $this->buffered[] = rtrim($line, "\n");

                return strlen($line);
            }
        );
        $buffer->method('readLine')->willReturnCallback(
            fn (): string => $this->buffered[$this->readAt++] ?? ''
        );

        $feed = $this->createMock(FileWriteInterface::class);
        $feed->method('writeCsv')->willReturnCallback(
            function (array $row): int {
                $this->written[] = array_map('strval', $row);

                return count($row);
            }
        );

        $directory = $this->createMock(WriteInterface::class);
        $directory->method('openFile')->willReturnCallback(
            fn (string $path): FileWriteInterface => $path === self::FEED_PATH ? $feed : $buffer
        );
        $directory->method('isExist')->willReturn(true);
        $directory->method('delete')->willReturn(true);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($directory);

        return $filesystem;
    }
}
