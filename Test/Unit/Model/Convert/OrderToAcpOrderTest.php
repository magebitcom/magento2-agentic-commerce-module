<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\Convert;

use Magebit\AcpSpec\Api\AgenticCheckout\OrderInterfaceFactory;
use Magebit\AcpSpec\Data\AgenticCheckout\Order as AcpOrder;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Model\Convert\OrderToAcpOrder;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;

class OrderToAcpOrderTest extends TestCase
{
    private const SESSION_ID = 'cs_abc123';
    private const PERMALINK = 'https://shop.test/agentic_commerce/checkout/order/order_id/cs_abc123/';

    /**
     * The three fields the spec marks required on the order object.
     *
     * @return void
     */
    public function testCarriesTheRequiredFields(): void
    {
        $order = $this->convert();

        $this->assertSame('000000123', $order->getId());
        $this->assertSame(self::SESSION_ID, $order->getCheckoutSessionId());
        $this->assertSame(self::PERMALINK, $order->getPermalinkUrl());
    }

    /**
     * The permalink is keyed by the session id, which is what the route resolves through the order
     * link. The dropped `ac_order_id` column used to hold that value and no longer holds anything.
     *
     * @return void
     */
    public function testThePermalinkIsBuiltFromTheSessionIdNotTheOrder(): void
    {
        $order = $this->convert();

        $this->assertStringContainsString(self::SESSION_ID, (string) $order->getPermalinkUrl());
    }

    /**
     * @return void
     */
    public function testReportsTheHumanReadableOrderNumber(): void
    {
        $this->assertSame('000000123', $this->convert()->getOrderNumber());
    }

    /**
     * @return void
     */
    public function testMapsTheStatusThroughTheMerchantsConfiguredMap(): void
    {
        $this->assertSame('shipped', $this->convert()->getStatus());
    }

    /**
     * @return void
     */
    public function testDiscriminatorIsTheOrderType(): void
    {
        $this->assertSame(OrderToAcpOrder::TYPE, $this->convert()->getType());
    }

    /**
     * @return AcpOrder
     */
    private function convert(): AcpOrder
    {
        $factory = $this->createMock(OrderInterfaceFactory::class);
        $factory->method('create')->willReturnCallback(static fn (): AcpOrder => new AcpOrder());

        $url = $this->createMock(UrlInterface::class);
        $url->method('getUrl')->willReturnCallback(
            static fn (string $route, array $params): string =>
                'https://shop.test/' . $route . '/order_id/' . $params['order_id'] . '/'
        );

        $config = $this->createMock(ConfigInterface::class);
        $config->method('getOrderStatusMap')->willReturn([
            ['magento_order_status' => 'complete', 'ac_status' => 'shipped'],
        ]);

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIncrementId', 'getStatus', 'getStoreId'])
            ->getMock();
        $order->method('getIncrementId')->willReturn('000000123');
        $order->method('getStatus')->willReturn('complete');
        $order->method('getStoreId')->willReturn(1);

        return (new OrderToAcpOrder($factory, $url, $config))->convert($order, self::SESSION_ID);
    }
}
