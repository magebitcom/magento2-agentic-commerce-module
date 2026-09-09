<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\Discovery;

use Magebit\AcpSpec\Api\AgenticCheckout\DiscoveryCapabilitiesInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscoveryProtocolInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscoveryResponseInterfaceFactory;
use Magebit\AcpSpec\Data\AgenticCheckout\DiscoveryCapabilities;
use Magebit\AcpSpec\Data\AgenticCheckout\DiscoveryProtocol;
use Magebit\AcpSpec\Data\AgenticCheckout\DiscoveryResponse;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Model\Discovery\DiscoveryDocumentBuilder;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class DiscoveryDocumentBuilderTest extends TestCase
{
    private DiscoveryDocumentBuilder $builder;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getCheckoutRouterBasePath')->willReturn('checkout_sessions');

        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->method('getBaseUrl')->willReturn('https://shop.example.com/');

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAvailableCurrencyCodes', 'getCurrentCurrencyCode'])
            ->getMock();
        $store->method('getAvailableCurrencyCodes')->willReturn(['EUR', 'USD']);
        $store->method('getCurrentCurrencyCode')->willReturn('EUR');

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $localeResolver = $this->createMock(ResolverInterface::class);
        $localeResolver->method('getLocale')->willReturn('en_US');

        $this->builder = new DiscoveryDocumentBuilder(
            $this->factory(DiscoveryResponseInterfaceFactory::class, DiscoveryResponse::class),
            $this->factory(DiscoveryProtocolInterfaceFactory::class, DiscoveryProtocol::class),
            $this->factory(DiscoveryCapabilitiesInterfaceFactory::class, DiscoveryCapabilities::class),
            $config,
            $urlBuilder,
            $storeManager,
            $localeResolver
        );
    }

    /**
     * @return void
     */
    public function testDocumentCarriesEveryRequiredField(): void
    {
        $document = $this->builder->build()->jsonSerialize();

        foreach (['protocol', 'api_base_url', 'transports', 'capabilities'] as $field) {
            $this->assertArrayHasKey($field, $document);
        }
    }

    /**
     * @return void
     */
    public function testProtocolNamesThePinnedRevision(): void
    {
        $protocol = $this->builder->build()->getProtocol();

        $this->assertNotNull($protocol);
        $this->assertSame('acp', $protocol->getName());
        $this->assertSame('2026-04-17', $protocol->getVersion());
        $this->assertSame(['2026-04-17'], $protocol->getSupportedVersions());
    }

    /**
     * Advertising a transport we do not serve would have agents call an endpoint that is not there.
     *
     * @return void
     */
    public function testOnlyTheRestTransportIsAdvertised(): void
    {
        $this->assertSame(['rest'], $this->builder->build()->getTransports());
    }

    /**
     * @return void
     */
    public function testApiBaseUrlIsAbsoluteAndHasNoDoubleSlash(): void
    {
        $url = $this->builder->build()->getApiBaseUrl();

        $this->assertSame('https://shop.example.com/checkout_sessions', $url);
    }

    /**
     * @return void
     */
    public function testCapabilitiesReportTheStoreCurrenciesAndLocale(): void
    {
        $capabilities = $this->builder->build()->getCapabilities();

        $this->assertNotNull($capabilities);
        $this->assertSame(['checkout'], $capabilities->getServices());
        $this->assertSame(['EUR', 'USD'], $capabilities->getSupportedCurrencies());
        $this->assertSame(['en_US'], $capabilities->getSupportedLocales());
    }

    /**
     * @param class-string $factoryClass Factory to stub
     * @param class-string $dtoClass Generated DTO it returns
     * @return object
     */
    private function factory(string $factoryClass, string $dtoClass): object
    {
        $factory = $this->createMock($factoryClass);
        $factory->method('create')->willReturnCallback(static fn (): object => new $dtoClass());

        return $factory;
    }
}
