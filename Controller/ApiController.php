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

abstract class ApiController implements ActionInterface, CsrfAwareActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     */
    public function __construct(
        protected readonly JsonFactory $resultJsonFactory,
        protected readonly RequestInterface $request
    ) {
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
        /** @var array<mixed> $data */
        $data = $errorResponse->getData();

        return $this->makeJsonResponse($data, $statusCode);
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
