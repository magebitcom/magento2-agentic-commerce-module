<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Export;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Symfony\Component\Console\Helper\ProgressBar;
use Generator;
use Magebit\AgenticCommerce\Api\Data\FeedProductInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magebit\AgenticCommerce\Api\ProductFeedWriterInterface;
use Magebit\AgenticCommerce\Api\ProductFeedWriterInterfaceFactory;
use Magebit\AgenticCommerce\Api\ProductMapperInterface;
use Magento\Catalog\Model\Product\Visibility;

class ProductFeed
{
    /**
     * @var null|ProgressBar
     */
    private ?ProgressBar $progressBar = null;

    /**
     * @var null|int
     */
    private ?int $storeId = null;

    /**
     * @var null|string
     */
    private ?string $feedFilePath = null;

    public function __construct(
        private readonly CollectionFactory $productCollectionFactory,
        private readonly ProductMapperInterface $productMapper,
        private readonly ProductFeedWriterInterfaceFactory $productFeedWriterFactory
    ) {
    }

    /**
     * Export process
     * @return array
     */
    public function export()
    {
        /** @var ProductFeedWriterInterface $productFeedWriter */
        $productFeedWriter = $this->productFeedWriterFactory->create();
        $productFeedWriter->setFeedFilePath($this->feedFilePath);

        foreach ($this->pageIterator() as $page => $collection) {
            $data = [];

            foreach ($collection as $product) {
                $data = array_merge($data, $this->mapProduct($product));
                $this->progressBar?->advance();
            }

            $productFeedWriter->write($data, $page);
        }

        return $data;
    }

    /**
     * @param ProductInterface $product
     * @return FeedProductInterface[]
     */
    public function mapProduct(ProductInterface $product): array
    {
        return $this->productMapper->map($product);
    }

    /**
     * @return Generator
     */
    protected function pageIterator(): Generator
    {
        $page = 1;

        do {
            $collection = $this->getCollection();
            $collection->setCurPage($page);
            $collection->load();

            if ($this->progressBar && !$this->progressBar->getMaxSteps()) {
                $this->progressBar->setMaxSteps($collection->getSize());
            }

            yield $page => $collection;
            $page++;

        } while ($collection->getCurPage() < $collection->getLastPageNumber());
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        /** @var Collection $collection */
        $collection = $this->productCollectionFactory->create();

        return $collection
            ->addAttributeToSelect('*')
            ->addPriceData()
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', [
                'in' => [
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_IN_SEARCH,
                    Visibility::VISIBILITY_BOTH,
                ]
            ])
            ->addStoreFilter($this->storeId)
            ->setPageSize($this->getPageSize());
    }

    /**
     * @param int $storeId
     * @return ProductFeed
     */
    public function setStoreId(int $storeId): self
    {
        $this->storeId = $storeId;

        return $this;
    }

    /**
     * @param ProgressBar $progressBar
     * @return ProductFeed
     */
    public function setProgressBar(ProgressBar $progressBar): self
    {
        $this->progressBar = $progressBar;

        return $this;
    }

    /**
     * @param string $feedFilePath
     * @return ProductFeed
     */
    public function setFeedFilePath(string $feedFilePath): self
    {
        $this->feedFilePath = $feedFilePath;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return 100;
    }
}
