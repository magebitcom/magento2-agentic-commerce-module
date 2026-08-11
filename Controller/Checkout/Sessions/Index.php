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
use Magebit\AgenticCommerce\Api\Data\Request\CreateCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CreateCheckoutSessionRequestInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magebit\AcpSpec\Data\AgenticCheckout\CheckoutSession;
use Magebit\AgenticCommerce\Controller\ApiController;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magebit\AgenticCommerce\Service\CheckoutSessionService;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Service\RequestValidationService;

class Index extends ApiController implements HttpPostActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param RequestValidationService $requestValidationService
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     * @param ComplianceService $complianceService
     * @param CreateCheckoutSessionRequestInterfaceFactory $checkoutSessionsRequestFactory
     * @param CheckoutSessionService $checkoutSessionService
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        RequestValidationService $requestValidationService,
        ErrorResponseInterfaceFactory $errorResponseFactory,
        protected readonly ComplianceService $complianceService,
        protected readonly CreateCheckoutSessionRequestInterfaceFactory $checkoutSessionsRequestFactory,
        protected readonly CheckoutSessionService $checkoutSessionService,
        protected readonly LoggerInterface $logger,
        protected readonly ConfigInterface $config
    ) {
        parent::__construct($resultJsonFactory, $request, $requestValidationService, $errorResponseFactory);
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

        /** @var CreateCheckoutSessionRequestInterface $checkoutSessionsRequest */
        $checkoutSessionsRequest = $this->createRequestObjectAndValidate($this->checkoutSessionsRequestFactory->create(...));

        if ($checkoutSessionsRequest instanceof ErrorResponseInterface) {
            return $this->makeErrorResponse($checkoutSessionsRequest);
        }

        try {
            $checkoutSessionResponse = $this->checkoutSessionService->create($checkoutSessionsRequest);

            /** @var CheckoutSession $checkoutSessionResponse */
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
