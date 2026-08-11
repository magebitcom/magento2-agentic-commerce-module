<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Controller;

use InvalidArgumentException;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magebit\AgenticCommerce\Model\Data\Response\ErrorResponse;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magento\Framework\DataObject;
use Magebit\AgenticCommerce\Service\RequestValidationService;

abstract class ApiController implements ActionInterface, CsrfAwareActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param RequestValidationService $requestValidationService
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     */
    public function __construct(
        protected readonly JsonFactory $resultJsonFactory,
        protected readonly RequestInterface $request,
        protected readonly RequestValidationService $requestValidationService,
        protected readonly ErrorResponseInterfaceFactory $errorResponseFactory
    ) {
    }

    /**
     * @template T of \Magebit\AgenticCommerce\Api\Data\Request\RequestInterface
     * @param callable(array<mixed>): T $factory
     * @return T|ErrorResponseInterface
     */
    protected function createRequestObjectAndValidate(callable $factory): mixed
    {
        /** @var Http $request */
        $request = $this->getRequest();

        /** @var string $content */
        $content = $request->getContent();
        $rawData = json_decode($content, true);

        if (!is_array($rawData)) {
            return $this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => 'Invalid request - array expected',
            ]]);
        }

        $requestObject = $factory(['data' => $rawData]);

        if ($validationError = $this->requestValidationService->validate($requestObject)) {
            return $validationError;
        }

        return $requestObject;
    }

    /**
     * @param ErrorResponseInterface $errorResponse
     * @param int $statusCode
     * @return ResultJson
     * @throws InvalidArgumentException
     */
    public function makeErrorResponse(ErrorResponseInterface $errorResponse, int $statusCode = 400): ResultJson
    {
        /** @var ErrorResponse $errorResponse */
        if ($errorResponse->getData('_statusCode')) {
            // @phpstan-ignore cast.int
            $statusCode = (int) $errorResponse->getData('_statusCode');
            $errorResponse->unsetData('_statusCode');
        }

        // The spec requires Retry-After on an in-flight idempotency collision.
        $retryAfter = $errorResponse->getData('_retryAfter');
        $errorResponse->unsetData('_retryAfter');

        /** @var array<mixed> $data */
        $data = $errorResponse->toArray();
        $response = $this->makeJsonResponse($data, $statusCode);

        if (is_numeric($retryAfter)) {
            $response->setHeader('Retry-After', (string) (int) $retryAfter, true);
        }

        return $response;
    }

    /**
     * @param array<mixed>|DataObject $data
     * @param int $statusCode
     * @return ResultJson
     */
    public function makeJsonResponse(array|DataObject $data, int $statusCode = 200): ResultJson
    {
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($data);
        $resultJson->setHttpResponseCode($statusCode);

        return $resultJson;
    }

    /**
     * @param ResultJson $resultJson
     * @param Http $request
     * @return void
     */
    public function addHeaders(ResultJson $resultJson, Http $request): void
    {
        $resultJson->setHeader('Idempotency-Key', (string) $request->getHeader('Idempotency-Key', ''));
        $resultJson->setHeader('API-Version', ComplianceService::API_VERSION);
        $resultJson->setHeader('Request-Id', (string) $request->getHeader('Request-Id', ''));
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
