<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magebit\AgenticCommerce\Controller\Checkout\Sessions;

use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CreateCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CreateCheckoutSessionRequestInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magebit\AgenticCommerce\Model\Data\Response\CheckoutSessionResponse;
use Magebit\AgenticCommerce\Controller\ApiController;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magebit\AgenticCommerce\Service\CheckoutSessionService;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magebit\AgenticCommerce\Service\ComplianceService;

class Index extends ApiController implements HttpPostActionInterface
{
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        protected readonly ComplianceService $complianceService,
        protected readonly ErrorResponseInterfaceFactory $errorResponseFactory,
        protected readonly CreateCheckoutSessionRequestInterfaceFactory $checkoutSessionsRequestFactory,
        protected readonly CheckoutSessionService $checkoutSessionService,
        protected readonly LoggerInterface $logger
    ) {
        parent::__construct($resultJsonFactory, $request);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $request = $this->getRequest();

        if ($validationError = $this->complianceService->validateRequest($request)) {
            return $this->makeErrorResponse($validationError);
        }

        /** @var Http $request */
        /** @var string $content */
        $content = $request->getContent();
        $rawData = json_decode($content, true);

        if (!is_array($rawData) || !isset($rawData['items']) || !is_array($rawData['items'])) {
            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => 'Invalid request',
            ]]));
        }

        /** @var CreateCheckoutSessionRequestInterface $checkoutSessionsRequest */
        $checkoutSessionsRequest = $this->checkoutSessionsRequestFactory->create(['data' => $rawData]);

        try {
            $checkoutSessionResponse = $this->checkoutSessionService->create($checkoutSessionsRequest);
        } catch (LocalizedException $e) {
            $this->logger->critical('[AgenticCommerce] Error creating checkout session', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_PROCESSING_ERROR,
                'code' => 'processing_error',
                'message' => $e->getMessage(),
            ]]), 500);
        } catch (\Exception $e) {
            $this->logger->critical('[AgenticCommerce] Error creating checkout session', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_PROCESSING_ERROR,
                'code' => 'internal_server_error',
                'message' => 'Internal server error',
            ]]), 500);
        }

        /** @var CheckoutSessionResponse $checkoutSessionResponse */
        return $this->makeJsonResponse($checkoutSessionResponse->toArray());
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function validateApiVersion(RequestInterface $request): bool
    {
        /** @var Http $request */
        return $request->getHeader('API-Version') === self::API_VERSION;
    }
}
