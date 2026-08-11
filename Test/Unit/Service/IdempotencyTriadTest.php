<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Service;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Api\Data\IdempotencyInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magebit\AgenticCommerce\Model\Data\Response\ErrorResponse;
use Magebit\AgenticCommerce\Model\Idempotency\Management;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use PHPUnit\Framework\TestCase;

/**
 * The three idempotency outcomes the spec requires: 400 when the key is absent, 409 while the
 * original request is still running, 422 when the same key carries a different body.
 */
class IdempotencyTriadTest extends TestCase
{
    private const KEY = 'idem_123';
    private const HASH = 'hash_of_this_body';

    /**
     * @return void
     */
    public function testMissingKeyIsRejectedWithFourHundred(): void
    {
        $error = $this->service()->validateIdempotency($this->request(null));

        $this->assertNotNull($error);
        $this->assertSame(ErrorResponseInterface::TYPE_INVALID_REQUEST, $error->getType());
        $this->assertSame(ErrorResponseInterface::CODE_IDEMPOTENCY_KEY_REQUIRED, $error->getCode());
        $this->assertSame(400, $this->statusOf($error));
    }

    /**
     * A GET carries no side effect to replay, so the requirement does not apply to it.
     *
     * @return void
     */
    public function testReadRequestsAreNotRequiredToCarryAKey(): void
    {
        $this->assertNull($this->service()->validateIdempotency($this->request(null, method: 'GET')));
    }

    /**
     * @return void
     */
    public function testKeyReusedWithADifferentBodyIsRejectedWithFourTwentyTwo(): void
    {
        $error = $this->service(
            $this->record(requestHash: 'hash_of_another_body', response: 'stored')
        )->validateIdempotency($this->request(self::KEY));

        $this->assertNotNull($error);
        $this->assertSame(ErrorResponseInterface::CODE_IDEMPOTENCY_CONFLICT, $error->getCode());
        $this->assertSame(422, $this->statusOf($error));
    }

    /**
     * A claimed key with no stored response is the original request, still running.
     *
     * @return void
     */
    public function testInFlightCollisionIsRejectedWithFourOhNineAndRetryAfter(): void
    {
        $error = $this->service($this->record(response: null))->validateIdempotency($this->request(self::KEY));

        $this->assertNotNull($error);
        $this->assertSame(ErrorResponseInterface::CODE_IDEMPOTENCY_IN_FLIGHT, $error->getCode());
        $this->assertSame(409, $this->statusOf($error));
        $this->assertGreaterThan(0, (int)$this->dataOf($error, '_retryAfter'));
    }

    /**
     * Losing the race to claim the key means another request is already doing the work.
     *
     * @return void
     */
    public function testLosingTheClaimRaceReportsInFlight(): void
    {
        $error = $this->service(null, reserved: false)->validateIdempotency($this->request(self::KEY));

        $this->assertNotNull($error);
        $this->assertSame(ErrorResponseInterface::CODE_IDEMPOTENCY_IN_FLIGHT, $error->getCode());
    }

    /**
     * @return void
     */
    public function testFirstRequestWithAFreshKeyProceeds(): void
    {
        $this->assertNull($this->service()->validateIdempotency($this->request(self::KEY)));
    }

    /**
     * A completed record with a matching body is a replay, handled downstream, not an error.
     *
     * @return void
     */
    public function testCompletedRecordWithTheSameBodyProceedsToReplay(): void
    {
        $this->assertNull(
            $this->service($this->record(response: 'stored'))->validateIdempotency($this->request(self::KEY))
        );
    }

    /**
     * @param IdempotencyInterface|null $existing Record already stored under the key
     * @param bool $reserved Whether the key could be claimed
     * @return ComplianceService
     */
    private function service(?IdempotencyInterface $existing = null, bool $reserved = true): ComplianceService
    {
        $management = $this->createMock(Management::class);
        $management->method('canHandleIdempotency')
            ->willReturnCallback(static fn (Http $r): bool => in_array($r->getMethod(), ['POST', 'PUT', 'PATCH'], true));
        $management->method('hashRequest')->willReturn(self::HASH);
        $management->method('getIdempotency')->willReturn($existing);
        $management->method('reserve')->willReturn($reserved);

        $errorFactory = $this->createMock(ErrorResponseInterfaceFactory::class);
        $errorFactory->method('create')->willReturnCallback(
            static fn (array $args = []): ErrorResponse => new ErrorResponse($args['data'] ?? [])
        );

        return new ComplianceService(
            $errorFactory,
            $management,
            $this->createMock(JsonFactory::class),
            $this->createMock(EncryptorInterface::class),
            $this->createMock(ConfigInterface::class)
        );
    }

    /**
     * @param string $requestHash Hash stored against the key
     * @param string|null $response Stored response, or null while the original is in flight
     * @return IdempotencyInterface
     */
    private function record(string $requestHash = self::HASH, ?string $response = null): IdempotencyInterface
    {
        $record = $this->createMock(IdempotencyInterface::class);
        $record->method('getRequestHash')->willReturn($requestHash);
        $record->method('getResponse')->willReturn($response);
        $record->method('getExpiresAt')->willReturn(date('Y-m-d H:i:s', time() + 3600));

        return $record;
    }

    /**
     * @param string|null $key Idempotency-Key header value, or null when absent
     * @param string $method HTTP method
     * @return Http
     */
    private function request(?string $key, string $method = 'POST'): Http
    {
        $request = $this->createMock(Http::class);
        $request->method('getHeader')->willReturn($key ?? false);
        $request->method('getMethod')->willReturn($method);

        return $request;
    }

    /**
     * @param ErrorResponseInterface $error Error under test
     * @return int
     */
    private function statusOf(ErrorResponseInterface $error): int
    {
        return (int)$this->dataOf($error, '_statusCode');
    }

    /**
     * @param ErrorResponseInterface $error Error under test
     * @param string $key Side-channel key carrying transport detail
     * @return mixed
     */
    private function dataOf(ErrorResponseInterface $error, string $key): mixed
    {
        /** @var ErrorResponse $error */
        return $error->getData($key);
    }
}
