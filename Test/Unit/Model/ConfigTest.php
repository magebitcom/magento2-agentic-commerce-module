<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;

/**
 * The seller name is the one feed setting a store can leave blank, because the specification makes
 * it optional. Declaring it as always a string crashed the whole export on such a store.
 */
class ConfigTest extends TestCase
{
    private const STORE_NAME_PATH = 'general/store_information/name';

    /**
     * @return void
     */
    public function testAnUnsetSellerNameIsReportedAsMissingRatherThanCrashing(): void
    {
        $this->assertNull($this->config([])->getSellerName(1));
    }

    /**
     * @return void
     */
    public function testABlankSellerNameIsReportedAsMissing(): void
    {
        $this->assertNull($this->config([self::STORE_NAME_PATH => '   '])->getSellerName(1));
    }

    /**
     * @return void
     */
    public function testTheStoreNameIsUsedWhenThatIsWhatTheSettingPointsAt(): void
    {
        $config = $this->config([self::STORE_NAME_PATH => 'Example Store']);

        $this->assertSame('Example Store', $config->getSellerName(1));
    }

    /**
     * @return void
     */
    public function testTheOwnSellerNameIsUsedWhenTheSettingPointsAtIt(): void
    {
        $config = $this->config([
            ConfigInterface::CONFIG_SELLER_NAME_SOURCE => 'custom',
            ConfigInterface::CONFIG_SELLER_NAME => 'Example Seller',
            self::STORE_NAME_PATH => 'Example Store',
        ]);

        $this->assertSame('Example Seller', $config->getSellerName(1));
    }

    /**
     * @return void
     */
    public function testSurroundingSpaceIsTrimmedFromTheName(): void
    {
        $config = $this->config([self::STORE_NAME_PATH => '  Example Store  ']);

        $this->assertSame('Example Store', $config->getSellerName(1));
    }

    /**
     * @param array<string, string> $values Configuration paths and their values
     * @return Config
     */
    private function config(array $values): Config
    {
        $values[ConfigInterface::CONFIG_SELLER_NAME_SOURCE] ??= 'general';

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(
            static fn (string $path, string $scope = '', mixed $scopeCode = null): ?string => $values[$path] ?? null
        );

        return new Config(
            $scopeConfig,
            $this->createMock(UrlInterface::class),
            $this->createMock(SerializerInterface::class)
        );
    }
}
