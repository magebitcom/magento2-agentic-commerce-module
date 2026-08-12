<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Service;

use Magebit\AgenticCore\Model\Idempotency\ClaimOutcome;
use Magebit\AgenticCore\Model\Idempotency\Coordinator;
use Magebit\AgenticCore\Model\Idempotency\RequestHasher;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magebit\AgenticCommerce\Api\ConfigInterface;

class ComplianceService
{
    /** Newest first — returned to clients as a preference list. */
    public const SUPPORTED_API_VERSIONS = [
        '2026-04-17',
        '2026-01-30',
        '2025-12-12',
        '2025-09-29',
    ];

    /** The version we emit, not the newest we accept. */
    public const API_VERSION = '2025-09-29';

    /** What a client waiting on an in-flight request is told to wait, in seconds. */
    public const IN_FLIGHT_RETRY_AFTER_SECONDS = 1;

    /**
     * Partitions this module's rows in the shared table.
     */
    public const IDEMPOTENCY_SCOPE = 'acp';

    /**
     * This protocol requires a key on writes, so the list is an allow-list of methods it applies to
     * rather than a deny-list of safe ones.
     */
    private const ALLOWED_METHODS = ['POST', 'PUT', 'PATCH'];

    /**
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     * @param Coordinator $coordinator
     * @param RequestHasher $hasher
     * @param JsonFactory $resultJsonFactory
     * @param EncryptorInterface $encryptor
     * @param ConfigInterface $config
     */
    public function __construct(
        protected readonly ErrorResponseInterfaceFactory $errorResponseFactory,
        protected readonly Coordinator $coordinator,
        protected readonly RequestHasher $hasher,
        protected readonly JsonFactory $resultJsonFactory,
        protected readonly EncryptorInterface $encryptor,
        protected readonly ConfigInterface $config
    ) {
    }

    /**
     * @param Http $request
     * @return bool
     */
    public function validateApiVersion(Http $request): bool
    {
        return in_array($this->getRequestedApiVersion($request), self::SUPPORTED_API_VERSIONS, true);
    }

    /**
     * @param Http $request
     * @return string
     */
    public function getRequestedApiVersion(Http $request): string
    {
        return trim((string) $request->getHeader('API-Version'));
    }

    /**
     * @param Http $request
     * @return bool
     */
    public function validateApiToken(Http $request): bool
    {
        $apiToken = $this->config->getApiToken();

        if (!$apiToken) {
            return true;
        }

        return $request->getHeader('Authorization') === 'Bearer ' . $apiToken;
    }

    /**
     * @param Http $request
     * @return null|ErrorResponseInterface
     */
    public function validateRequest(Http $request): ?ErrorResponseInterface
    {
        if (!$this->validateApiVersion($request)) {
            $requestedVersion = $this->getRequestedApiVersion($request);

            return $this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => $requestedVersion === ''
                    ? 'missing_api_version'
                    : 'unsupported_api_version',
                'message' => $requestedVersion === ''
                    ? 'The API-Version header is required.'
                    : 'The requested API version is not supported.',
                ErrorResponseInterface::KEY_SUPPORTED_VERSIONS => self::SUPPORTED_API_VERSIONS,
            ]]);
        }

        if (!$this->validateApiToken($request)) {
            return $this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_api_token',
                'message' => 'Invalid API token',
                '_statusCode' => 401,
            ]]);
        }

        if ($idempotencyError = $this->validateIdempotency($request)) {
            return $idempotencyError;
        }

        return null;
    }

    /**
     * @param Http $request
     * @return null|ErrorResponseInterface
     */
    public function validateIdempotency(Http $request): ?ErrorResponseInterface
    {
        if (!$this->appliesTo($request)) {
            return null;
        }

        $key = trim((string) $request->getHeader('Idempotency-Key'));

        if ($key === '') {
            return $this->idempotencyError(
                ErrorResponseInterface::CODE_IDEMPOTENCY_KEY_REQUIRED,
                'Idempotency-Key header is required',
                400
            );
        }

        $result = $this->coordinator->claim(self::IDEMPOTENCY_SCOPE, $key, $this->hasher->hash($request));

        return match ($result->outcome) {
            // A stored response is replayed by handleIdempotency(), not rejected here.
            ClaimOutcome::Claimed, ClaimOutcome::Replay => null,
            ClaimOutcome::InFlight => $this->inFlightError(),
            ClaimOutcome::Conflict => $this->idempotencyError(
                ErrorResponseInterface::CODE_IDEMPOTENCY_CONFLICT,
                'Idempotency-Key has already been used with a different request body',
                422
            ),
        };
    }

    /**
     * @param Http $request
     * @return bool
     */
    private function appliesTo(Http $request): bool
    {
        return in_array(strtoupper($request->getMethod()), self::ALLOWED_METHODS, true);
    }

    /**
     * @return ErrorResponseInterface
     */
    private function inFlightError(): ErrorResponseInterface
    {
        return $this->idempotencyError(
            ErrorResponseInterface::CODE_IDEMPOTENCY_IN_FLIGHT,
            'A request with this Idempotency-Key is currently being processed',
            409,
            self::IN_FLIGHT_RETRY_AFTER_SECONDS
        );
    }

    /**
     * Idempotency violations are all `invalid_request`; only the code and status differ.
     *
     * @param string $code
     * @param string $message
     * @param int $statusCode
     * @param int|null $retryAfter Seconds to advertise in Retry-After, when the client should wait
     * @return ErrorResponseInterface
     */
    private function idempotencyError(
        string $code,
        string $message,
        int $statusCode,
        ?int $retryAfter = null
    ): ErrorResponseInterface {
        $data = [
            'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
            'code' => $code,
            'message' => $message,
            '_statusCode' => $statusCode,
        ];

        if ($retryAfter !== null) {
            $data['_retryAfter'] = $retryAfter;
        }

        /** @var ErrorResponseInterface $error */
        $error = $this->errorResponseFactory->create(['data' => $data]);

        return $error;
    }

    /**
     * Expiry is now `created_at` plus the TTL read at purge time, so a TTL change also applies to
     * rows already written.
     *
     * @param Http $request
     * @return null|ResultJson
     */
    public function handleIdempotency(Http $request): ?ResultJson
    {
        $key = trim((string) $request->getHeader('Idempotency-Key'));

        if ($key === '' || !$this->appliesTo($request)) {
            return null;
        }

        $result = $this->coordinator->claim(self::IDEMPOTENCY_SCOPE, $key, $this->hasher->hash($request));

        if ($result->outcome !== ClaimOutcome::Replay || $result->record === null) {
            return null;
        }

        return $this->resultJsonFactory
            ->create()
            ->setJsonData((string) $this->encryptor->decrypt((string) $result->record->getResponseBody()))
            ->setHttpResponseCode((int) $result->record->getResponseStatus());
    }

    /**
     * @param Http $request
     * @param string $response
     * @param int $status
     * @return void
     */
    public function storeResponse(Http $request, string $response, int $status): void
    {
        $key = trim((string) $request->getHeader('Idempotency-Key'));

        if ($key === '' || !$this->appliesTo($request)) {
            return;
        }

        $this->coordinator->storeResponse(
            self::IDEMPOTENCY_SCOPE,
            $key,
            $this->hasher->hash($request),
            $status,
            // Encryption stays here rather than moving into the shared base: the other consumer
            // stores plaintext, and unifying that inside an extraction would change one module's
            // storage while attributing any regression to the other's refactor.
            $this->encryptor->encrypt($response)
        );
    }
}
