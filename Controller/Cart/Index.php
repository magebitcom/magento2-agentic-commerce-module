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
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use JsonSerializable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

use Magebit\AgenticCommerce\Api\Data\Request\CartCreateRequestInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CartCreateRequestInterfaceFactory;

class Index extends ApiController implements HttpPostActionInterface
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
     * @param CartCreateRequestInterfaceFactory $cartRequestFactory
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        RequestValidator $requestValidator,
        Hydrator $hydrator,
        ErrorResponseInterfaceFactory $errorResponseFactory,
        ComplianceService $complianceService,
        protected readonly LoggerInterface $logger,
        protected readonly CartServiceInterface $cartService,
        protected readonly ConfigInterface $config,
        protected readonly CartCreateRequestInterfaceFactory $cartRequestFactory
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

        $request = $this->getHttpRequest();

        if ($response = $this->guard($request)) {
            return $response;
        }

        /** @var CartCreateRequestInterface $cartRequest */
        $cartRequest = $this->createRequestObjectAndValidate(
            CartCreateRequestInterface::class,
            $this->cartRequestFactory->create(...)
        );

        if ($cartRequest instanceof ErrorResponseInterface) {
            return $this->makeErrorResponse($cartRequest);
        }

        try {
            $cart = $this->cartService->create($cartRequest);

            if (!$cart instanceof JsonSerializable) {
                throw new LocalizedException(__('The cart could not be serialised.'));
            }

            return $this->respond($request, $cart, 200);
        } catch (NoSuchEntityException $e) {
            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'cart_not_found',
                'message' => 'Cart not found',
            ]]), 404);
        } catch (LocalizedException $e) {
            $this->logger->critical('[AgenticCommerce] Error creating cart', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => $e->getLogMessage(),
            ]]));
        } catch (\Exception $e) {
            $this->logger->critical('[AgenticCommerce] Error creating cart', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_PROCESSING_ERROR,
                'code' => 'internal_server_error',
                'message' => 'Internal server error',
            ]]), 500);
        }
    }
}
