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
use Magebit\AcpSpec\Data\AgenticCheckout\CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magebit\AgenticCommerce\Api\Data\Request\CompleteCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CompleteCheckoutSessionRequestInterfaceFactory;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCore\Model\Request\Hydrator;
use Magebit\AgenticCore\Model\Validation\RequestValidator;

class Complete extends ApiController implements HttpPostActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param RequestValidator $requestValidator
     * @param Hydrator $hydrator
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     * @param ComplianceService $complianceService
     * @param LoggerInterface $logger
     * @param CheckoutSessionService $checkoutSessionService
     * @param CompleteCheckoutSessionRequestInterfaceFactory $checkoutSessionsRequestFactory
     * @param ConfigInterface $config
     * @param RequestValidator $requestValidator
     * @param Hydrator $hydrator
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        RequestValidator $requestValidator,
        Hydrator $hydrator,
        ErrorResponseInterfaceFactory $errorResponseFactory,
        ComplianceService $complianceService,
        protected readonly LoggerInterface $logger,
        protected readonly CheckoutSessionService $checkoutSessionService,
        protected readonly CompleteCheckoutSessionRequestInterfaceFactory $checkoutSessionsRequestFactory,
        protected readonly ConfigInterface $config,
    ) {
        parent::__construct(
            $resultJsonFactory,
            $request,
            $requestValidator,
            $hydrator,
            $errorResponseFactory,
            $complianceService
        );
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

        if ($response = $this->guard($request)) {
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

        /** @var CompleteCheckoutSessionRequestInterface $checkoutSessionsRequest */
        $checkoutSessionsRequest = $this->createRequestObjectAndValidate(
            CompleteCheckoutSessionRequestInterface::class,
            $this->checkoutSessionsRequestFactory->create(...)
        );

        if ($checkoutSessionsRequest instanceof ErrorResponseInterface) {
            return $this->makeErrorResponse($checkoutSessionsRequest);
        }

        try {
            $checkoutSessionResponse = $this->checkoutSessionService->complete($sessionId, $checkoutSessionsRequest);

            /** @var CheckoutSession $checkoutSessionResponse */
            $responseData = $checkoutSessionResponse->toArray();
            return $this->respond($request, $responseData);
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
