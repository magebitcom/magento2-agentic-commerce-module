<?php
/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);

namespace Magebit\AgenticCommerce\Controller\Delegate\Payment;

use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magebit\AgenticCommerce\Controller\ApiController;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magebit\AgenticCore\Model\Request\Hydrator;
use Magebit\AgenticCore\Model\Validation\RequestValidator;
use Magebit\AcpSpec\Api\DelegatePayment\DelegatePaymentRequestInterfaceFactory;
use Magebit\AgenticCommerce\Service\DelegatePaymentService;
use Magebit\AcpSpec\Data\DelegatePayment\DelegatePaymentResponse;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magebit\AcpSpec\Api\DelegatePayment\DelegatePaymentRequestInterface;

class Index extends ApiController implements HttpPostActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param RequestValidator $requestValidator
     * @param Hydrator $hydrator
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     * @param ComplianceService $complianceService
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        RequestValidator $requestValidator,
        Hydrator $hydrator,
        ErrorResponseInterfaceFactory $errorResponseFactory,
        protected readonly ComplianceService $complianceService,
        protected readonly DelegatePaymentRequestInterfaceFactory $delegatePaymentRequestFactory,
        protected readonly DelegatePaymentService $delegatePaymentService,
        protected readonly LoggerInterface $logger,
    ) {
        parent::__construct(
            $resultJsonFactory,
            $request,
            $requestValidator,
            $hydrator,
            $errorResponseFactory
        );
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Http $request */
        $request = $this->getRequest();

        if ($validationError = $this->complianceService->validateRequest($request)) {
            return $this->makeErrorResponse($validationError);
        }

        if ($response = $this->complianceService->handleIdempotency($request)) {
            $this->addHeaders($response, $request);
            return $response;
        }

        $delegatePaymentRequest = $this->createRequestObjectAndValidate(
            DelegatePaymentRequestInterface::class,
            $this->delegatePaymentRequestFactory->create(...)
        );

        if ($delegatePaymentRequest instanceof ErrorResponseInterface) {
            return $this->makeErrorResponse($delegatePaymentRequest);
        }

        try {
            $delegatePaymentResponse = $this->delegatePaymentService->storePaymentMethod($delegatePaymentRequest);

            /** @var DelegatePaymentResponse $delegatePaymentResponse */
            $responseData = $delegatePaymentResponse->toArray();
            $this->complianceService->storeResponse($request, (string) json_encode($responseData), 200);

            $response = $this->makeJsonResponse($responseData);
            $this->addHeaders($response, $request);
            return $response;
        } catch (LocalizedException $e) {
            $this->logger->critical('[AgenticCommerce] Error storing payment method', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => $e->getLogMessage(),
            ]]));
        } catch (\Exception $e) {
            $this->logger->critical('[AgenticCommerce] Error storing payment method', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_PROCESSING_ERROR,
                'code' => 'internal_server_error',
                'message' => 'Internal server error',
            ]]), 500);
        }
    }
}
