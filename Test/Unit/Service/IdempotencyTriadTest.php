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

use Magebit\AgenticCore\Model\Idempotency\ClaimOutcome;
use Magebit\AgenticCore\Model\Idempotency\ClaimResult;
use Magebit\AgenticCore\Model\Idempotency\Coordinator;
use Magebit\AgenticCore\Model\Idempotency\Gate;
use Magebit\AgenticCore\Model\Idempotency\RequestHasher;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magebit\AgenticCommerce\Model\Data\Response\ErrorResponse;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json as ResultJson;
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

    /**
     * @return void
     */
    public function testMissingKeyIsRejectedWithFourHundred(): void
    {
        $error = $this->errorFrom($this->service()->guard($this->request(null)));

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
        $this->assertNull($this->service()->guard($this->request(null, method: 'GET')));
    }

    /**
     * @return void
     */
    public function testKeyReusedWithADifferentBodyIsRejectedWithFourTwentyTwo(): void
    {
        $error = $this->errorFrom($this->service(ClaimOutcome::Conflict)->guard($this->request(self::KEY)));

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
        $error = $this->errorFrom($this->service(ClaimOutcome::InFlight)->guard($this->request(self::KEY)));

        $this->assertNotNull($error);
        $this->assertSame(ErrorResponseInterface::CODE_IDEMPOTENCY_IN_FLIGHT, $error->getCode());
        $this->assertSame(409, $this->statusOf($error));
        $this->assertGreaterThan(0, (int) $this->dataOf($error, '_retryAfter'));
    }

    /**
     * @return void
     */
    public function testFirstRequestWithAFreshKeyProceeds(): void
    {
        $this->assertNull($this->service(ClaimOutcome::Claimed)->guard($this->request(self::KEY)));
    }

    /**
     * A completed record with a matching body is replayed rather than refused, so what comes back is
     * a response to send, not an error.
     *
     * @return void
     */
    public function testCompletedRecordWithTheSameBodyIsReplayed(): void
    {
        $outcome = $this->service(ClaimOutcome::Replay)->guard($this->request(self::KEY));

        $this->assertNotInstanceOf(ErrorResponseInterface::class, $outcome);
    }

    /**
     * This module had no takeover: a request that died before storing a response wedged its key
     * until the TTL expired, so an agent retrying a crashed call got 409 rather than proceeding.
     * The shared coordinator reports Claimed once it has taken the abandoned claim over.
     *
     * @return void
     */
    public function testAnAbandonedClaimLetsTheRetryProceed(): void
    {
        $this->assertNull($this->service(ClaimOutcome::Claimed)->guard($this->request(self::KEY)));
    }

    /**
     * @param ClaimOutcome|null $outcome What the coordinator reports for this key
     * @return ComplianceService
     */
    private function service(?ClaimOutcome $outcome = null): ComplianceService
    {
        $coordinator = $this->createMock(Coordinator::class);
        $coordinator->method('claim')->willReturn(new ClaimResult($outcome ?? ClaimOutcome::Claimed));

        $errorFactory = $this->createMock(ErrorResponseInterfaceFactory::class);
        $errorFactory->method('create')->willReturnCallback(
            static fn (array $args = []): ErrorResponse => new ErrorResponse($args['data'] ?? [])
        );

        $gate = new Gate(
            $coordinator,
            new RequestHasher(),
            $this->createMock(EncryptorInterface::class),
            ['POST', 'PUT', 'PATCH'],
            true
        );

        $result = $this->createMock(ResultJson::class);
        $result->method('setJsonData')->willReturn($result);
        $result->method('setHttpResponseCode')->willReturn($result);

        $resultJsonFactory = $this->createMock(JsonFactory::class);
        $resultJsonFactory->method('create')->willReturn($result);

        return new ComplianceService(
            $errorFactory,
            $gate,
            $resultJsonFactory,
            $this->createMock(ConfigInterface::class)
        );
    }

    /**
     * @param string|null $key Idempotency-Key header value, or null when absent
     * @param string $method HTTP method
     * @return Http
     */
    private function request(?string $key, string $method = 'POST'): Http
    {
        $headers = [
            'API-Version' => ComplianceService::SUPPORTED_API_VERSIONS[0],
            'Idempotency-Key' => $key ?? false,
        ];

        $request = $this->createMock(Http::class);
        $request->method('getHeader')->willReturnCallback(
            static fn (string $name): string|bool => $headers[$name] ?? false
        );
        $request->method('getMethod')->willReturn($method);
        $request->method('getPathInfo')->willReturn('/agentic/checkout');
        $request->method('getQuery')->willReturn([]);
        $request->method('getContent')->willReturn('{}');

        return $request;
    }

    /**
     * @param mixed $outcome What guard() returned
     * @return ErrorResponseInterface
     */
    private function errorFrom(mixed $outcome): ErrorResponseInterface
    {
        $this->assertInstanceOf(ErrorResponseInterface::class, $outcome);

        return $outcome;
    }

    /**
     * @param ErrorResponseInterface $error Error under test
     * @return int
     */
    private function statusOf(ErrorResponseInterface $error): int
    {
        return (int) $this->dataOf($error, '_statusCode');
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
