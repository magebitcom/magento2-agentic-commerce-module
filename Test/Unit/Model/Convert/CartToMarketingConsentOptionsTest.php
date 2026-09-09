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

use Magebit\AcpSpec\Api\AgenticCheckout\MarketingConsentOptionInterfaceFactory;
use Magebit\AcpSpec\Data\AgenticCheckout\MarketingConsentOption;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Model\Convert\CartToMarketingConsentOptions;
use Magebit\AgenticCommerce\Model\MarketingConsent\HandlerPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class CartToMarketingConsentOptionsTest extends TestCase
{
    /**
     * @return void
     */
    public function testDeclaresAChannelThatHasAHandlerAndALabel(): void
    {
        $options = $this->convert(channels: ['email']);

        $this->assertCount(1, $options);
        $this->assertSame('email', $options[0]->getChannel());
        $this->assertSame('promotional emails', $options[0]->getDisplayText());
        $this->assertSame('https://shop.test/privacy', $options[0]->getPrivacyPolicyUrl());
    }

    /**
     * The store must never advertise a channel it has no way to honour, so a channel with no handler is
     * not declared even if a label exists for it.
     *
     * @return void
     */
    public function testAChannelWithNoHandlerIsNotDeclared(): void
    {
        $this->assertSame([], $this->convert(channels: []));
    }

    /**
     * @return void
     */
    public function testAChannelWithNoLabelIsNotDeclared(): void
    {
        $this->assertSame([], $this->convert(channels: ['sms']));
    }

    /**
     * The config getter throws when the URL is unset. Declaring nothing is the answer — and it must not
     * take the whole checkout down with it, which is what an uncaught throw would do.
     *
     * @return void
     */
    public function testNoPrivacyPolicyMeansNoOptionsRatherThanAFailure(): void
    {
        $this->assertSame([], $this->convert(channels: ['email'], privacyPolicyUrl: null));
    }

    /**
     * @param string[] $channels
     * @param string|null $privacyPolicyUrl
     * @return array<int, MarketingConsentOption>
     */
    private function convert(array $channels, ?string $privacyPolicyUrl = 'https://shop.test/privacy'): array
    {
        $factory = $this->createMock(MarketingConsentOptionInterfaceFactory::class);
        $factory->method('create')->willReturnCallback(
            static fn (): MarketingConsentOption => new MarketingConsentOption()
        );

        $pool = $this->createMock(HandlerPool::class);
        $pool->method('getChannels')->willReturn($channels);

        $config = $this->createMock(ConfigInterface::class);

        if ($privacyPolicyUrl === null) {
            $config->method('getSellerPrivacyPolicyUrl')
                ->willThrowException(new LocalizedException(__('not configured')));
        } else {
            $config->method('getSellerPrivacyPolicyUrl')->willReturn($privacyPolicyUrl);
        }

        $cart = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStoreId'])
            ->getMock();
        $cart->method('getStoreId')->willReturn(1);

        /** @var array<int, MarketingConsentOption> $options */
        $options = (new CartToMarketingConsentOptions(
            $factory,
            $pool,
            $config,
            ['email' => 'promotional emails']
        ))->execute($cart);

        return $options;
    }
}
