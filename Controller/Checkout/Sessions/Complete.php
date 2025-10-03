<?php
/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Controller\Checkout\Sessions;

use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magebit\AgenticCommerce\Controller\ApiController;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magebit\AgenticCommerce\Service\CheckoutSessionService;
use Psr\Log\LoggerInterface;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magebit\AgenticCommerce\Model\Data\Response\CheckoutSessionResponse;
use Magento\Framework\Exception\LocalizedException;
use Magebit\AgenticCommerce\Api\Data\Request\CompleteCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CompleteCheckoutSessionRequestInterfaceFactory;
use Magebit\AgenticCommerce\Api\ConfigInterface;

class Complete extends ApiController implements HttpPostActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param ComplianceService $complianceService
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     * @param LoggerInterface $logger
     * @param CheckoutSessionService $checkoutSessionService
     * @param CompleteCheckoutSessionRequestInterfaceFactory $checkoutSessionsRequestFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        protected readonly ComplianceService $complianceService,
        protected readonly ErrorResponseInterfaceFactory $errorResponseFactory,
        protected readonly LoggerInterface $logger,
        protected readonly CheckoutSessionService $checkoutSessionService,
        protected readonly CompleteCheckoutSessionRequestInterfaceFactory $checkoutSessionsRequestFactory,
        protected readonly ConfigInterface $config
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
        if (!$this->config->isCheckoutEnabled()) {
            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'checkout_disabled',
                'message' => 'Checkout is disabled',
            ]]));
        }

        /** @var Http $request */
        $request = $this->getRequest();

        if ($validationError = $this->complianceService->validateRequest($request)) {
            return $this->makeErrorResponse($validationError);
        }

        if ($response = $this->complianceService->handleIdempotency($request)) {
            $this->addHeaders($response, $request);
            return $response;
        }

        /** @var string|null $sessionId */
        $sessionId = $request->getParam('session_id');

        if (!$sessionId) {
            $this->logger->critical('Session ID is required');

            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => 'Invalid request',
            ]]));
        }

        /** @var string $content */
        $content = $request->getContent();
        $rawData = json_decode($content, true);

        if (!is_array($rawData) || !isset($rawData['payment_data']) || !is_array($rawData['payment_data'])) {
            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => 'Invalid request',
                'path' => '$.payment_data',
            ]]));
        }

        /** @var CompleteCheckoutSessionRequestInterface $checkoutSessionsRequest */
        $checkoutSessionsRequest = $this->checkoutSessionsRequestFactory->create(['data' => $rawData]);

        try {
            $checkoutSessionResponse = $this->checkoutSessionService->complete($sessionId, $checkoutSessionsRequest);

            /** @var CheckoutSessionResponse $checkoutSessionResponse */
            $responseData = $checkoutSessionResponse->toArray();
            $this->complianceService->storeResponse($request, (string) json_encode($responseData), 200);

            $response = $this->makeJsonResponse($responseData);
            $this->addHeaders($response, $request);
            return $response;
        } catch (LocalizedException $e) {
            $this->logger->critical('[AgenticCommerce] Error creating checkout session', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => $e->getLogMessage(),
            ]]));
        } catch (\Exception $e) {
            $this->logger->critical('[AgenticCommerce] Error creating checkout session', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_PROCESSING_ERROR,
                'code' => 'internal_server_error',
                'message' => 'Internal server error',
            ]]), 500);
        }
    }
}
