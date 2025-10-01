<?php

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
use Magebit\AgenticCommerce\Api\ProductMapperInterface;

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

    public function __construct(
        private readonly CollectionFactory $productCollectionFactory,
        private readonly ProductMapperInterface $productMapper,
        private readonly ProductFeedWriterInterface $productFeedWriter
    ) {
    }

    /**
     * Export process
     * @return array
     */
    public function export()
    {
        foreach ($this->pageIterator() as $page => $collection) {
            $data = [];

            foreach ($collection as $product) {
                $data[$product->getId()] = $this->getFeedProduct($product);
                $this->progressBar?->advance();
            }

            $this->productFeedWriter->write($data, $page);
        }

        return $data;
    }

    /**
     * @param ProductInterface $product
     * @return FeedProductInterface
     */
    public function getFeedProduct(ProductInterface $product): FeedProductInterface
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
     * @return int
     */
    public function getPageSize(): int
    {
        return 100;
    }
}
