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
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magebit\AgenticCommerce\Service\CheckoutSessionService;
use Psr\Log\LoggerInterface;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magebit\AgenticCommerce\Model\Data\Response\CheckoutSessionResponse;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magebit\AgenticCommerce\Api\ConfigInterface;

class Retrieve extends ApiController implements HttpGetActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param ComplianceService $complianceService
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     * @param LoggerInterface $logger
     * @param CheckoutSessionService $checkoutSessionService
     * @param ConfigInterface $config
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        protected readonly ComplianceService $complianceService,
        protected readonly ErrorResponseInterfaceFactory $errorResponseFactory,
        protected readonly LoggerInterface $logger,
        protected readonly CheckoutSessionService $checkoutSessionService,
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

        $sessionId = $request->getParam('session_id');

        if (!$sessionId) {
            $this->logger->critical('Session ID is required');

            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => 'Invalid request',
            ]]));
        }

        try {

            /** @var string $sessionId */
            /** @var CheckoutSessionResponse $response */
            $response = $this->checkoutSessionService->retrieve((string) $sessionId);
            $response = $this->makeJsonResponse($response);

            $this->addHeaders($response, $request);
            return $response;
        } catch (NoSuchEntityException $e) {
            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'session_not_found',
                'message' => 'Checkout session not found',
            ]]), 404);
        } catch (LocalizedException $e) {
            $this->logger->critical('[AgenticCommerce] Error retrieving checkout session', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => $e->getLogMessage(),
            ]]));
        } catch (\Exception $e) {
            $this->logger->critical('[AgenticCommerce] Error retrieving checkout session', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create([ 'data' => [
                'type' => ErrorResponseInterface::TYPE_PROCESSING_ERROR,
                'code' => 'internal_server_error',
                'message' => 'Internal server error',
            ]]), 500);
        }
    }
}
