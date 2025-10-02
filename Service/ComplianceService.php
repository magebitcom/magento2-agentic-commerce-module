<?php

namespace Magebit\AgenticCommerce\Service;

use Magento\Framework\App\Request\Http;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;

class ComplianceService
{
    public const API_VERSION = '2025-10-01';

    /**
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     */
    public function __construct(
        protected readonly ErrorResponseInterfaceFactory $errorResponseFactory,
    ) {
    }

    /**
     * @param Http $request
     * @return bool
     */
    public function validateApiVersion(Http $request): bool
    {
        return $request->getHeader('API-Version') === self::API_VERSION;
    }

    /**
     * @param Http $request
     * @return null|ErrorResponseInterface
     */
    public function validateRequest(Http $request): ?ErrorResponseInterface
    {
        if (!$this->validateApiVersion($request)) {
            return $this->errorResponseFactory->create([
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_api_version',
                'message' => 'Invalid API version',
            ]);
        }

        return null;
    }
}
