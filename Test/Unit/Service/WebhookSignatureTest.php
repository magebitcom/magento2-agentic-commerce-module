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

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Service\WebhookService;
use Magento\Framework\HTTP\Client\CurlFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class WebhookSignatureTest extends TestCase
{
    private const SECRET = 'whsec_test';
    private const PAYLOAD = '{"type":"order_updated"}';
    private const TIMESTAMP = 1709123456;

    private WebhookService $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getWebhookSecret')->willReturn(self::SECRET);

        $this->service = new class (
            $this->createMock(CurlFactory::class),
            $config,
            $this->createMock(LoggerInterface::class)
        ) extends WebhookService {
            /**
             * @param string $payload
             * @param int $timestamp
             * @return string
             */
            public function signature(string $payload, int $timestamp): string
            {
                return $this->getSignature($payload, $timestamp);
            }
        };
    }

    /**
     * @return void
     */
    public function testSignatureMatchesTheSpecPattern(): void
    {
        $signature = $this->service->signature(self::PAYLOAD, self::TIMESTAMP);

        $this->assertMatchesRegularExpression('/^t=\d+,v1=[a-fA-F0-9]{64}$/', $signature);
    }

    /**
     * The digest covers `timestamp + "." + raw_body`, not the body alone; signing only the body is
     * what makes a captured request replayable.
     *
     * @return void
     */
    public function testDigestCoversTheTimestampAndBody(): void
    {
        $expected = hash_hmac('sha256', self::TIMESTAMP . '.' . self::PAYLOAD, self::SECRET);

        $this->assertSame(
            't=' . self::TIMESTAMP . ',v1=' . $expected,
            $this->service->signature(self::PAYLOAD, self::TIMESTAMP)
        );
    }

    /**
     * @return void
     */
    public function testTheSameBodyAtAnotherTimeSignsDifferently(): void
    {
        $this->assertNotSame(
            $this->service->signature(self::PAYLOAD, self::TIMESTAMP),
            $this->service->signature(self::PAYLOAD, self::TIMESTAMP + 1)
        );
    }

    /**
     * @return void
     */
    public function testTheTimestampInTheHeaderIsTheOneThatWasSigned(): void
    {
        $signature = $this->service->signature(self::PAYLOAD, self::TIMESTAMP);
        [$timestampPart] = explode(',', $signature);

        $this->assertSame('t=' . self::TIMESTAMP, $timestampPart);
    }
}
