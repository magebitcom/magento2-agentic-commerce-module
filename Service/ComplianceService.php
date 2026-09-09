<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Service;

use Magebit\AgenticCore\Model\Idempotency\DecisionOutcome;
use Magebit\AgenticCore\Model\Idempotency\Gate;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
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
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     * @param Gate $gate
     * @param JsonFactory $resultJsonFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        protected readonly ErrorResponseInterfaceFactory $errorResponseFactory,
        protected readonly Gate $gate,
        protected readonly JsonFactory $resultJsonFactory,
        protected readonly ConfigInterface $config
    ) {
    }

    /**
     * Everything that has to hold before the operation runs. Returns what to send instead of
     * running it — a refusal, or the response a previous identical request already got — or null to
     * go ahead. Asked once per request, because claiming the key twice reads as a collision with
     * this same request.
     *
     * @param Http $request
     * @return ErrorResponseInterface|ResultJson|null
     */
    public function guard(Http $request): ErrorResponseInterface|ResultJson|null
    {
        if ($error = $this->validateRequest($request)) {
            return $error;
        }

        $decision = $this->gate->decide(self::IDEMPOTENCY_SCOPE, $request);

        return match ($decision->outcome) {
            DecisionOutcome::Proceed => null,
            DecisionOutcome::Replay => $this->resultJsonFactory
                ->create()
                ->setJsonData((string) $decision->body)
                ->setHttpResponseCode((int) $decision->status),
            DecisionOutcome::KeyMissing => $this->idempotencyError(
                ErrorResponseInterface::CODE_IDEMPOTENCY_KEY_REQUIRED,
                'Idempotency-Key header is required',
                400
            ),
            DecisionOutcome::InFlight => $this->idempotencyError(
                ErrorResponseInterface::CODE_IDEMPOTENCY_IN_FLIGHT,
                'A request with this Idempotency-Key is currently being processed',
                409,
                self::IN_FLIGHT_RETRY_AFTER_SECONDS
            ),
            DecisionOutcome::Conflict => $this->idempotencyError(
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

        return null;
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
     * @param Http $request
     * @param string $response
     * @param int $status
     * @return void
     */
    public function storeResponse(Http $request, string $response, int $status): void
    {
        $this->gate->remember(self::IDEMPOTENCY_SCOPE, $request, $response, $status);
    }
}
