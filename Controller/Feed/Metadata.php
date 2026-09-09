<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Controller\Feed;

use JsonSerializable;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterfaceFactory;
use Magebit\AgenticCommerce\Api\FeedServiceInterface;
use Magebit\AgenticCommerce\Controller\ApiController;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magebit\AgenticCore\Model\Request\Hydrator;
use Magebit\AgenticCore\Model\Validation\RequestValidator;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Metadata extends ApiController implements HttpGetActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param RequestValidator $requestValidator
     * @param Hydrator $hydrator
     * @param ErrorResponseInterfaceFactory $errorResponseFactory
     * @param ComplianceService $complianceService
     * @param LoggerInterface $logger
     * @param FeedServiceInterface $feedService
     * @param ConfigInterface $config
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        RequestValidator $requestValidator,
        Hydrator $hydrator,
        ErrorResponseInterfaceFactory $errorResponseFactory,
        ComplianceService $complianceService,
        protected readonly LoggerInterface $logger,
        protected readonly FeedServiceInterface $feedService,
        protected readonly ConfigInterface $config
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
        if (!$this->config->isFeedEnabled()) {
            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'feed_disabled',
                'message' => 'Feed is disabled',
            ]]));
        }

        /** @var Http $request */
        $request = $this->getRequest();

        if ($response = $this->guard($request)) {
            return $response;
        }

        $feedId = $request->getParam('feed_id');
        $feedId = is_scalar($feedId) ? (string) $feedId : '';

        if ($feedId === '') {
            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => 'A feed identifier is required',
            ]]));
        }

        try {
            $payload = $this->feedService->metadata($feedId);

            if (!$payload instanceof JsonSerializable) {
                throw new LocalizedException(__('The feed could not be serialised.'));
            }

            $response = $this->makeJsonResponse($payload, 200);

            $this->addHeaders($response, $request);

            return $response;
        } catch (NoSuchEntityException $e) {
            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'feed_not_found',
                'message' => 'Feed not found',
            ]]), 404);
        } catch (LocalizedException $e) {
            $this->logger->critical('[AgenticCommerce] Error retrieving feed', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_INVALID_REQUEST,
                'code' => 'invalid_request',
                'message' => $e->getLogMessage(),
            ]]));
        } catch (\Exception $e) {
            $this->logger->critical('[AgenticCommerce] Error retrieving feed', ['exception' => $e]);

            return $this->makeErrorResponse($this->errorResponseFactory->create(['data' => [
                'type' => ErrorResponseInterface::TYPE_PROCESSING_ERROR,
                'code' => 'internal_server_error',
                'message' => 'Internal server error',
            ]]), 500);
        }
    }
}
