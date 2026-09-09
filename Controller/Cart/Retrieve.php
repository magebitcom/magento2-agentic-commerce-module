<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Controller\Cart;

use Magebit\AgenticCommerce\Api\CartServiceInterface;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magebit\AgenticCommerce\Controller\ApiController;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magebit\AgenticCore\Model\Request\Hydrator;
use Magebit\AgenticCore\Model\Validation\RequestValidator;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use JsonSerializable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Retrieve extends ApiController implements HttpGetActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param RequestValidator $requestValidator
     * @param Hydrator $hydrator
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     * @param ComplianceService $complianceService
     * @param LoggerInterface $logger
     * @param CartServiceInterface $cartService
     * @param ConfigInterface $config
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        RequestValidator $requestValidator,
        Hydrator $hydrator,
        ErrorResponseInterfaceFactory $errorResponseFactory,
        protected readonly ComplianceService $complianceService,
        protected readonly LoggerInterface $logger,
        protected readonly CartServiceInterface $cartService,
        protected readonly ConfigInterface $config
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
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if (!$this->config->isCheckoutEnabled()) {
            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
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

        $cartId = $request->getParam('cart_id');
        $cartId = is_scalar($cartId) ? (string) $cartId : '';

        if ($cartId === '') {
            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => 'A cart identifier is required',
            ]]));
        }

        try {
            $cart = $this->cartService->retrieve($cartId);

            if (!$cart instanceof JsonSerializable) {
                throw new LocalizedException(__('The cart could not be serialised.'));
            }

            $response = $this->makeJsonResponse($cart);

            $this->addHeaders($response, $request);

            return $response;
        } catch (NoSuchEntityException $e) {
            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'cart_not_found',
                'message' => 'Cart not found',
            ]]), 404);
        } catch (LocalizedException $e) {
            $this->logger->critical('[AgenticCommerce] Error retrieving cart', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => $e->getLogMessage(),
            ]]));
        } catch (\Exception $e) {
            $this->logger->critical('[AgenticCommerce] Error retrieving cart', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_PROCESSING_ERROR,
                'code' => 'internal_server_error',
                'message' => 'Internal server error',
            ]]), 500);
        }
    }
}
