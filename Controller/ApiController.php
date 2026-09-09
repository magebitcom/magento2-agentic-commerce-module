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
use JsonSerializable;
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
use Magebit\AgenticCore\Model\Request\Hydrator;
use Magebit\AgenticCore\Model\Validation\RequestValidator;
use Magebit\AgenticCore\Model\Validation\ValidationResult;
use Magento\Framework\DataObject;

abstract class ApiController implements ActionInterface, CsrfAwareActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param RequestValidator $requestValidator
     * @param Hydrator $hydrator
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     */
    public function __construct(
        protected readonly JsonFactory $resultJsonFactory,
        protected readonly RequestInterface $request,
        protected readonly RequestValidator $requestValidator,
        protected readonly Hydrator $hydrator,
        protected readonly ErrorResponseInterfaceFactory $errorResponseFactory
    ) {
    }

    /**
     * Reads the body, checks it against the interface the specification generated, and fills the
     * request object from it. Nothing about the shape is stated here: the interface carries it all.
     *
     * @template T of object
     * @param class-string<T> $interface Generated interface the body must match
     * @param callable(): T $factory Builds the empty request object
     * @return T|ErrorResponseInterface
     */
    protected function createRequestObjectAndValidate(string $interface, callable $factory): mixed
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

        $result = $this->requestValidator->validate($rawData, $interface);

        if (!$result->isValid()) {
            return $this->validationResultToResponse($result);
        }

        $requestObject = $factory();
        $this->hydrator->populateWithArray($requestObject, $rawData, $interface);

        return $requestObject;
    }

    /**
     * The first complaint is reported, since `param` names one field and the spec's error carries
     * one error.
     *
     * @param ValidationResult $result
     * @return ErrorResponseInterface
     */
    protected function validationResultToResponse(ValidationResult $result): ErrorResponseInterface
    {
        $errors = $result->getErrors();
        $path = (string) array_key_first($errors);

        /** @var ErrorResponseInterface $error */
        $error = $this->errorResponseFactory->create(['data' => [
            'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
            'code' => 'invalid_request',
            'message' => (string) reset($errors),
            'param' => $this->jsonPath($path),
        ]]);

        return $error;
    }

    /**
     * The validator reports dot-notation paths; `param` is a JSONPath, which roots at `$` and
     * brackets list positions.
     *
     * @param string $dotted
     * @return string
     */
    protected function jsonPath(string $dotted): string
    {
        return '$.' . preg_replace('/\.(\d+)(?=\.|$)/', '[$1]', $dotted);
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
     * @param array<mixed>|DataObject|JsonSerializable $data
     * @param int $statusCode
     * @return ResultJson
     */
    public function makeJsonResponse(array|DataObject|JsonSerializable $data, int $statusCode = 200): ResultJson
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
