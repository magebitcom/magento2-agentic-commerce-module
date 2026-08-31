<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Service;

use Magebit\AcpSpec\Api\Feed\FeedMetadataInterface;
use Magebit\AcpSpec\Api\Feed\FeedMetadataInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\ProductInterface as FeedProductInterface;
use Magebit\AcpSpec\Api\Feed\ProductsResponseInterface;
use Magebit\AcpSpec\Api\Feed\ProductsResponseInterfaceFactory;
use Magebit\AgenticCommerce\Api\FeedServiceInterface;
use Magebit\AgenticCommerce\Exception\FeedNotFoundException;
use Magebit\AgenticCommerce\Model\Convert\Feed\ProductToFeedProduct;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Serves the ACP feed. Nothing is stored: a feed is the store's catalogue for one target country, the id
 * encodes that country, and the products are read live so the feed cannot go stale against the catalogue.
 */
class FeedService implements FeedServiceInterface
{
    /**
     * Prefix making the id recognisable as ours and the country recoverable from it.
     */
    public const ID_PREFIX = 'feed_';

    public const MAX_LIMIT = 500;
    public const DEFAULT_LIMIT = 100;

    private const STOCK_FILTER_FLAG = 'has_stock_status_filter';

    /**
     * @param FeedMetadataInterfaceFactory $metadataFactory
     * @param ProductsResponseInterfaceFactory $productsResponseFactory
     * @param ProductToFeedProduct $productConverter
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param DirectoryHelper $directoryHelper
     * @param ImageHelper $imageHelper
     * @param Visibility $visibility
     * @param DateTime $dateTime
     */
    public function __construct(
        private readonly FeedMetadataInterfaceFactory $metadataFactory,
        private readonly ProductsResponseInterfaceFactory $productsResponseFactory,
        private readonly ProductToFeedProduct $productConverter,
        private readonly CollectionFactory $collectionFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly DirectoryHelper $directoryHelper,
        private readonly ImageHelper $imageHelper,
        private readonly Visibility $visibility,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * @inheritDoc
     */
    public function create(?string $targetCountry): FeedMetadataInterface
    {
        $country = $this->normaliseCountry($targetCountry);

        return $this->metadataFor(self::ID_PREFIX . strtolower($country), $country);
    }

    /**
     * @inheritDoc
     * @throws FeedNotFoundException
     */
    public function metadata(string $feedId): FeedMetadataInterface
    {
        return $this->metadataFor($feedId, $this->countryOf($feedId));
    }

    /**
     * @inheritDoc
     * @throws FeedNotFoundException
     */
    public function products(string $feedId, ?int $limit = null, int $offset = 0): ProductsResponseInterface
    {
        // Validates the id before reading anything, so an unknown feed is a not-found rather than an
        // empty catalogue that looks like a real answer.
        $this->countryOf($feedId);

        $collection = $this->sellableProducts();
        $ids = array_values(array_unique(array_map('intval', $collection->getAllIds())));
        $pageIds = array_slice($ids, max(0, $offset), $this->pageSize($limit));

        $products = [];

        foreach ($this->loadByIds($pageIds) as $product) {
            $products[] = $this->convert($product);
        }

        /** @var ProductsResponseInterface $response */
        $response = $this->productsResponseFactory->create();
        $response->setProducts($products);

        return $response;
    }

    /**
     * @param string $id
     * @param string $country
     * @return FeedMetadataInterface
     */
    private function metadataFor(string $id, string $country): FeedMetadataInterface
    {
        /** @var FeedMetadataInterface $metadata */
        $metadata = $this->metadataFactory->create();
        $metadata->setId($id);
        $metadata->setTargetCountry($country);
        $metadata->setUpdatedAt($this->dateTime->gmtDate('Y-m-d\TH:i:s\Z'));

        return $metadata;
    }

    /**
     * @param string $feedId
     * @return string
     * @throws FeedNotFoundException
     */
    private function countryOf(string $feedId): string
    {
        $country = strtoupper((string) preg_replace('~^' . preg_quote(self::ID_PREFIX, '~') . '~', '', $feedId));

        if ($country === $feedId || !preg_match('~^[A-Z]{2}$~', $country)) {
            throw new FeedNotFoundException($feedId);
        }

        return $country;
    }

    /**
     * A country the store does not sell to has no feed, so it is refused rather than answered with the
     * catalogue of somewhere else.
     *
     * @param string|null $targetCountry
     * @return string
     * @throws FeedNotFoundException
     */
    private function normaliseCountry(?string $targetCountry): string
    {
        $store = $this->storeManager->getStore();
        $default = $store instanceof Store ? (string) $this->directoryHelper->getDefaultCountry($store) : 'US';
        $country = strtoupper(trim((string) ($targetCountry ?? $default)));

        if (!preg_match('~^[A-Z]{2}$~', $country)) {
            throw new FeedNotFoundException($country);
        }

        return $country;
    }

    /**
     * @param MagentoProduct $product
     * @return FeedProductInterface
     */
    private function convert(MagentoProduct $product): FeedProductInterface
    {
        return $this->productConverter->execute(
            $product,
            $this->currencyCode(),
            $this->childrenOf($product),
            (string) $product->getProductUrl(),
            (string) $this->imageHelper->init($product, 'product_page_image_large')->getUrl()
        );
    }

    /**
     * @param MagentoProduct $product
     * @return MagentoProduct[]
     */
    private function childrenOf(MagentoProduct $product): array
    {
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return [];
        }

        $type = $product->getTypeInstance();

        if (!$type instanceof Configurable) {
            return [];
        }

        $children = [];

        foreach ($type->getUsedProducts($product) as $child) {
            if ($child instanceof MagentoProduct) {
                $children[] = $child;
            }
        }

        return $children;
    }

    /**
     * @param int[] $ids
     * @return MagentoProduct[]
     */
    private function loadByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $collection = $this->sellableProducts();
        $collection->addIdFilter($ids);

        $products = [];

        foreach ($collection as $product) {
            if ($product instanceof MagentoProduct) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * @return Collection
     */
    private function sellableProducts(): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(['name', 'description', 'price', 'image', 'status', 'visibility']);
        $collection->addAttributeToFilter('status', ['eq' => Status::STATUS_ENABLED]);
        $collection->setVisibility($this->visibility->getVisibleInSiteIds());
        $collection->addStoreFilter((int) $this->storeManager->getStore()->getId());

        // Out-of-stock products stay in the feed and report themselves unavailable, which is more use to
        // an agent than an absence it cannot interpret.
        $collection->setFlag(self::STOCK_FILTER_FLAG, true);

        return $collection;
    }

    /**
     * @param int|null $limit
     * @return int
     */
    private function pageSize(?int $limit): int
    {
        if ($limit === null || $limit < 1) {
            return self::DEFAULT_LIMIT;
        }

        return min($limit, self::MAX_LIMIT);
    }

    /**
     * @return string
     */
    private function currencyCode(): string
    {
        $store = $this->storeManager->getStore();

        return $store instanceof Store ? (string) $store->getCurrentCurrencyCode() : 'USD';
    }
}
