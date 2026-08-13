<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\MarketingConsent;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Model\MarketingConsent\NewsletterSubscription;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    /**
     * @var SubscriptionManagerInterface&MockObject
     */
    private SubscriptionManagerInterface $manager;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->manager = $this->createMock(SubscriptionManagerInterface::class);
    }

    /**
     * Off unless the merchant turns it on in admin: an agent relaying an opt-in is second-hand consent.
     *
     * @return void
     */
    public function testNothingHappensWhileDisabled(): void
    {
        $this->manager->expects($this->never())->method('subscribe');

        $this->handler(enabled: false)->handle('email', true, $this->order());
    }

    /**
     * @return void
     */
    public function testAnOptInSubscribes(): void
    {
        $this->manager->expects($this->once())->method('subscribe')->with('ada@example.com', 1);

        $this->handler()->handle('email', true, $this->order());
    }

    /**
     * Unsubscribing needs the subscriber's own confirmation code, so a buyer who was never subscribed
     * is left alone rather than having one invented for them.
     *
     * @return void
     */
    public function testAnOptOutForSomeoneNeverSubscribedDoesNothing(): void
    {
        $this->manager->expects($this->never())->method('unsubscribe');

        $this->handler(isSubscribed: false)->handle('email', false, $this->order());
    }

    /**
     * @return void
     */
    public function testAnOptOutUnsubscribesWithTheStoredCode(): void
    {
        $this->manager->expects($this->once())->method('unsubscribe')->with('ada@example.com', 1, 'code-42');

        $this->handler(isSubscribed: true)->handle('email', false, $this->order());
    }

    /**
     * @return void
     */
    public function testAnOrderWithNoEmailIsSkipped(): void
    {
        $this->manager->expects($this->never())->method('subscribe');

        $this->handler()->handle('email', true, $this->order(email: ''));
    }

    /**
     * @param bool $enabled
     * @param bool $isSubscribed
     * @return NewsletterSubscription
     */
    private function handler(bool $enabled = true, bool $isSubscribed = false): NewsletterSubscription
    {
        $subscriber = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByEmail', 'isSubscribed', 'getCode'])
            ->getMock();
        $subscriber->method('isSubscribed')->willReturn($isSubscribed);
        $subscriber->method('getCode')->willReturn('code-42');

        $factory = $this->createMock(SubscriberFactory::class);
        $factory->method('create')->willReturn($subscriber);

        $config = $this->createMock(ConfigInterface::class);
        $config->method('marketingConsentSubscribes')->willReturn($enabled);

        return new NewsletterSubscription($this->manager, $factory, $config);
    }

    /**
     * @param string $email
     * @return OrderInterface
     */
    private function order(string $email = 'ada@example.com'): OrderInterface
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomerEmail')->willReturn($email);
        $order->method('getStoreId')->willReturn(1);

        return $order;
    }
}
