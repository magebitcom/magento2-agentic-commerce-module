<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Service;

use LDAP\Result;
use Magebit\AgenticCommerce\Api\Data\IdempotencyInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magebit\AgenticCommerce\Model\Idempotency\Management as IdempotencyManagement;
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

    /**
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     * @param IdempotencyManagement $idempotencyManagement
     * @param JsonFactory $resultJsonFactory
     * @param EncryptorInterface $encryptor
     * @param ConfigInterface $config
     */
    public function __construct(
        protected readonly ErrorResponseInterfaceFactory $errorResponseFactory,
        protected readonly IdempotencyManagement $idempotencyManagement,
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
        $idempotency = $this->getIdempotency($request);

        if (!$idempotency) {
            return null;
        }

        $requestHash = $this->idempotencyManagement->hashRequest($request);

        if ($idempotency->getRequestHash() !== $requestHash) {
            return $this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_REQUEST_NOT_IDEMPOTENT,
                'code' => 'request_not_idempotent',
                'message' => 'Used same idempotency key for a different request',
            ]]);
        }

        return null;
    }

    /**
     * @param Http $request
     * @return null|ResultJson
     */
    public function handleIdempotency(Http $request): ?ResultJson
    {
        $idempotency = $this->getIdempotency($request);

        if (!$idempotency) {
            return null;
        }

        if ($idempotency->getExpiresAt() < date('Y-m-d H:i:s')) {
            return null;
        }

        return $this->resultJsonFactory
            ->create()
            ->setJsonData((string) $this->encryptor->decrypt((string) $idempotency->getResponse()))
            ->setHttpResponseCode((int) $idempotency->getStatus());
    }

    /**
     * @param Http $request
     * @param string $response
     * @param int $status
     * @return null|IdempotencyInterface
     */
    public function storeResponse(Http $request, string $response, int $status): ?IdempotencyInterface
    {
        return $this->idempotencyManagement->storeResponse($request, $response, $status);
    }

    /**
     * @param Http $request
     * @return null|IdempotencyInterface
     */
    protected function getIdempotency(Http $request): ?IdempotencyInterface
    {
        $idempotencyKey = $request->getHeader('Idempotency-Key');

        if (!$idempotencyKey || !$this->idempotencyManagement->canHandleIdempotency($request)) {
            return null;
        }

        $idempotency = $this->idempotencyManagement->getIdempotency((string) $idempotencyKey);

        if (!$idempotency) {
            return null;
        }

        return $idempotency;
    }
}
