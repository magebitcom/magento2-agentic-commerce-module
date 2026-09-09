<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Controller\Checkout;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magebit\AgenticCommerce\Service\ComplianceService;
use Magebit\AgenticCore\Api\OrderLinkRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\Redirect;

class Order implements HttpGetActionInterface
{
    /**
     * @param RedirectFactory $redirectFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderLinkRepositoryInterface $orderLinkRepository
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param Session $session
     */
    public function __construct(
        protected readonly RedirectFactory $redirectFactory,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly OrderLinkRepositoryInterface $orderLinkRepository,
        protected readonly RequestInterface $request,
        protected readonly ManagerInterface $messageManager,
        protected readonly Session $session
    ) {
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->request;
        /** @var string|null $orderId */
        $orderId = $request->getParam('order_id');

        if (!$orderId) {
            $this->messageManager->addErrorMessage((string) __('Missing order ID'));
            return $this->redirectFactory->create()->setPath('/');
        }

        $linkedOrderId = $this->orderLinkRepository->findOrderId(
            ComplianceService::IDEMPOTENCY_SCOPE,
            $orderId
        );

        if ($linkedOrderId === null) {
            $this->messageManager->addErrorMessage((string) __('Order not found'));
            return $this->redirectFactory->create()->setPath('/');
        }

        try {
            $order = $this->orderRepository->get($linkedOrderId);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage((string) __('Order not found'));
            return $this->redirectFactory->create()->setPath('/');
        }

        $this->session
            ->setLastQuoteId($order->getQuoteId())
            ->setLastSuccessQuoteId($order->getQuoteId())
            ->setLastOrderId($order->getEntityId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());

        $redirect = $this->redirectFactory->create();
        $redirect->setPath('checkout/onepage/success');
        return $redirect;
    }
}
