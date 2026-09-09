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
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * A feed page is read from the database one page at a time, so a small limit never costs a walk over
 * the whole catalogue.
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
     * @param MagentoProduct[] $page
     * @return FeedService
     */
    private function service(array $page = []): FeedService
    {
        $this->collections = [];
        $this->selects = [$this->createMock(Select::class), $this->createMock(Select::class)];

        $pages = [$this->collection($page, $this->selects[0])];

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
     * @return ProductToFeedProduct&MockObject
     */
    private function converter(): ProductToFeedProduct
    {
        $converter = $this->createMock(ProductToFeedProduct::class);
        $converter->method('execute')->willReturnCallback(
            static fn (MagentoProduct $product): FeedProductInterface
                => new FeedProduct(['id' => (string) $product->getSku()])
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
