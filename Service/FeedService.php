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
     * The only attributes the feed reads, so parents and children are selected the same way.
     */
    private const FEED_ATTRIBUTES = ['name', 'description', 'price', 'image', 'status', 'visibility'];

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

        $page = $this->page($limit, $offset);
        $childrenByParent = $this->childrenForPage($page);
        $products = [];

        foreach ($page as $product) {
            $products[] = $this->convert($product, $this->childrenOf($product, $childrenByParent));
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
     * The page is cut in the query, because the response carries no total and no paging links, so
     * nothing needs the ids of the whole catalogue.
     *
     * @param int|null $limit
     * @param int $offset
     * @return MagentoProduct[]
     */
    private function page(?int $limit, int $offset): array
    {
        $collection = $this->sellableProducts();

        // Without a fixed order the database is free to return rows differently every time, which
        // would make two pages overlap or skip products.
        $collection->addAttributeToSort('entity_id', Collection::SORT_ORDER_ASC);
        $collection->getSelect()->limit($this->pageSize($limit), max(0, $offset));

        $products = [];

        foreach ($collection as $product) {
            if ($product instanceof MagentoProduct) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * Every configurable on the page gets its children from one shared collection, so a page costs a
     * single child load instead of one per parent.
     *
     * @param MagentoProduct[] $page
     * @return array<int, MagentoProduct[]>
     */
    private function childrenForPage(array $page): array
    {
        $idsByParent = [];

        foreach ($page as $product) {
            $childIds = $this->childIdsOf($product);

            if ($childIds !== []) {
                $idsByParent[(int) $product->getId()] = $childIds;
            }
        }

        if ($idsByParent === []) {
            return [];
        }

        $loaded = $this->loadChildren(array_merge(...array_values($idsByParent)));
        $childrenByParent = [];

        foreach ($idsByParent as $parentId => $childIds) {
            foreach ($childIds as $childId) {
                if (isset($loaded[$childId])) {
                    $childrenByParent[$parentId][] = $loaded[$childId];
                }
            }
        }

        return $childrenByParent;
    }

    /**
     * @param MagentoProduct $product
     * @return int[]
     */
    private function childIdsOf(MagentoProduct $product): array
    {
        $type = $product->getTypeInstance();

        if ($product->getTypeId() !== Configurable::TYPE_CODE || !$type instanceof Configurable) {
            return [];
        }

        $ids = [];

        foreach ($type->getChildrenIds((int) $product->getId()) as $group) {
            foreach ((array) $group as $childId) {
                $ids[] = (int) $childId;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param int[] $ids
     * @return array<int, MagentoProduct>
     */
    private function loadChildren(array $ids): array
    {
        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            return [];
        }

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(self::FEED_ATTRIBUTES);
        $collection->addStoreFilter((int) $this->storeManager->getStore()->getId());
        $collection->addIdFilter($ids);
        $collection->setFlag(self::STOCK_FILTER_FLAG, true);

        $children = [];

        foreach ($collection as $product) {
            if ($product instanceof MagentoProduct) {
                $children[(int) $product->getId()] = $product;
            }
        }

        return $children;
    }

    /**
     * @param MagentoProduct $product
     * @param array<int, MagentoProduct[]> $childrenByParent
     * @return MagentoProduct[]
     */
    private function childrenOf(MagentoProduct $product, array $childrenByParent): array
    {
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return [];
        }

        return $childrenByParent[(int) $product->getId()] ?? [];
    }

    /**
     * @param MagentoProduct $product
     * @param MagentoProduct[] $children
     * @return FeedProductInterface
     */
    private function convert(MagentoProduct $product, array $children): FeedProductInterface
    {
        return $this->productConverter->execute(
            $product,
            $this->currencyCode(),
            $children,
            (string) $product->getProductUrl(),
            (string) $this->imageHelper->init($product, 'product_page_image_large')->getUrl()
        );
    }

    /**
     * @return Collection
     */
    private function sellableProducts(): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(self::FEED_ATTRIBUTES);
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
