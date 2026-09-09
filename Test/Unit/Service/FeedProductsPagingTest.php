<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Service;

use Magebit\AcpSpec\Api\Feed\FeedMetadataInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\ProductInterface as FeedProductInterface;
use Magebit\AcpSpec\Api\Feed\ProductsResponseInterfaceFactory;
use Magebit\AcpSpec\Data\Feed\Product as FeedProduct;
use Magebit\AcpSpec\Data\Feed\ProductsResponse;
use Magebit\AgenticCommerce\Exception\FeedNotFoundException;
use Magebit\AgenticCommerce\Model\Convert\Feed\ProductToFeedProduct;
use Magebit\AgenticCommerce\Service\FeedService;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * A feed page is read from the database one page at a time, and the children of every configurable on
 * that page come from a single extra load.
 */
class FeedProductsPagingTest extends TestCase
{
    private const FEED_ID = 'feed_us';

    /**
     * @var Collection[]
     */
    private array $collections = [];

    /**
     * @var Select[]
     */
    private array $selects = [];

    /**
     * @var array<int, array{0: MagentoProduct, 1: string, 2: MagentoProduct[]}>
     */
    private array $converted = [];

    /**
     * A caller asking for more than the cap gets the cap, so one request can never pull the whole
     * catalogue.
     *
     * @return void
     * @throws FeedNotFoundException
     */
    public function testThePageSizeIsCappedAtTheMaximum(): void
    {
        $service = $this->service();

        $this->selects[0]->expects($this->once())
            ->method('limit')
            ->with(FeedService::MAX_LIMIT, 25);

        $service->products(self::FEED_ID, 5000, 25);
    }

    /**
     * @return void
     * @throws FeedNotFoundException
     */
    public function testAPageWithNoLimitAsksForTheDefaultSize(): void
    {
        $service = $this->service();

        $this->selects[0]->expects($this->once())
            ->method('limit')
            ->with(FeedService::DEFAULT_LIMIT, 0);

        $service->products(self::FEED_ID);
    }

    /**
     * @return void
     * @throws FeedNotFoundException
     */
    public function testTheChildrenOfAConfigurableReachTheOutput(): void
    {
        $parent = $this->configurable(1, [11, 12]);
        $children = [$this->simple(11), $this->simple(12)];

        $response = $this->service([$parent], $children)->products(self::FEED_ID);

        $this->assertCount(1, $response->getProducts());
        $this->assertSame($children, $this->converted[0][2]);
    }

    /**
     * One load for the page and one for its children, however many configurables the page holds.
     *
     * @return void
     * @throws FeedNotFoundException
     */
    public function testAllConfigurablesOnThePageShareOneChildLoad(): void
    {
        $page = [$this->configurable(1, [11]), $this->configurable(2, [12])];

        $this->service($page, [$this->simple(11), $this->simple(12)])->products(self::FEED_ID);

        $this->assertCount(2, $this->collections);
    }

    /**
     * @return void
     * @throws FeedNotFoundException
     */
    public function testASimpleProductHasNoChildrenAndCostsNoExtraLoad(): void
    {
        $this->service([$this->simple(1)])->products(self::FEED_ID);

        $this->assertSame([], $this->converted[0][2]);
        $this->assertCount(1, $this->collections);
    }

    /**
     * @param MagentoProduct[] $page
     * @param MagentoProduct[] $children
     * @return FeedService
     */
    private function service(array $page = [], array $children = []): FeedService
    {
        $this->collections = [];
        $this->selects = [$this->createMock(Select::class), $this->createMock(Select::class)];
        $this->converted = [];

        $pages = [$this->collection($page, $this->selects[0]), $this->collection($children, $this->selects[1])];

        $factory = $this->createMock(CollectionFactory::class);
        $factory->method('create')->willReturnCallback(
            function () use ($pages): Collection {
                $collection = $pages[count($this->collections)] ?? $this->collection([], $this->selects[1]);
                $this->collections[] = $collection;

                return $collection;
            }
        );

        return new FeedService(
            $this->createMock(FeedMetadataInterfaceFactory::class),
            $this->responseFactory(),
            $this->converter(),
            $factory,
            $this->storeManager(),
            $this->createMock(DirectoryHelper::class),
            $this->imageHelper(),
            $this->createMock(Visibility::class),
            $this->createMock(DateTime::class)
        );
    }

    /**
     * @param MagentoProduct[] $items
     * @param Select&MockObject $select
     * @return Collection&MockObject
     */
    private function collection(array $items, Select $select): Collection
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'addAttributeToSelect',
                'addAttributeToFilter',
                'addAttributeToSort',
                'addStoreFilter',
                'addIdFilter',
                'setVisibility',
                'setFlag',
                'getSelect',
                'getIterator'
            ])
            ->getMock();
        $collection->method('getSelect')->willReturn($select);
        $collection->method('getIterator')->willReturn(new \ArrayIterator($items));

        return $collection;
    }

    /**
     * @param int $id
     * @param int[] $childIds
     * @return MagentoProduct&MockObject
     */
    private function configurable(int $id, array $childIds): MagentoProduct
    {
        $type = $this->createMock(Configurable::class);
        $type->method('getChildrenIds')->willReturn([0 => array_combine($childIds, $childIds)]);

        $product = $this->product($id, Configurable::TYPE_CODE);
        $product->method('getTypeInstance')->willReturn($type);

        return $product;
    }

    /**
     * @param int $id
     * @return MagentoProduct&MockObject
     */
    private function simple(int $id): MagentoProduct
    {
        return $this->product($id, 'simple');
    }

    /**
     * @param int $id
     * @param string $typeId
     * @return MagentoProduct&MockObject
     */
    private function product(int $id, string $typeId): MagentoProduct
    {
        $product = $this->getMockBuilder(MagentoProduct::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getSku', 'getTypeId', 'getTypeInstance', 'getProductUrl'])
            ->getMock();
        $product->method('getId')->willReturn($id);
        $product->method('getSku')->willReturn('sku-' . $id);
        $product->method('getTypeId')->willReturn($typeId);
        $product->method('getProductUrl')->willReturn('https://store.test/sku-' . $id);

        return $product;
    }

    /**
     * @return ProductToFeedProduct&MockObject
     */
    private function converter(): ProductToFeedProduct
    {
        $converter = $this->createMock(ProductToFeedProduct::class);
        $converter->method('execute')->willReturnCallback(
            function (
                MagentoProduct $product,
                string $currencyCode,
                array $children = []
            ): FeedProductInterface {
                $this->converted[] = [$product, $currencyCode, $children];

                return new FeedProduct(['id' => (string) $product->getSku()]);
            }
        );

        return $converter;
    }

    /**
     * @return ProductsResponseInterfaceFactory&MockObject
     */
    private function responseFactory(): ProductsResponseInterfaceFactory
    {
        $factory = $this->createMock(ProductsResponseInterfaceFactory::class);
        $factory->method('create')->willReturnCallback(static fn (): ProductsResponse => new ProductsResponse());

        return $factory;
    }

    /**
     * @return StoreManagerInterface&MockObject
     */
    private function storeManager(): StoreManagerInterface
    {
        $store = $this->createMock(Store::class);
        $store->method('getId')->willReturn(1);
        $store->method('getCurrentCurrencyCode')->willReturn('USD');

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return $storeManager;
    }

    /**
     * @return ImageHelper&MockObject
     */
    private function imageHelper(): ImageHelper
    {
        $helper = $this->createMock(ImageHelper::class);
        $helper->method('init')->willReturnSelf();
        $helper->method('getUrl')->willReturn('https://store.test/media/image.jpg');

        return $helper;
    }
}
