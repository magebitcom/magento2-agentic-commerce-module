<?php

namespace Magebit\AgenticCommerce\Model\Data\Request;

use Magebit\AgenticCommerce\Api\Data\Request\CompleteCheckoutSessionRequestInterface;
use Magebit\AgenticCommerce\Api\Data\BuyerInterface;
use Magebit\AgenticCommerce\Api\Data\PaymentDataInterface;
use Magebit\AgenticCommerce\Api\Data\PaymentDataInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\BuyerInterfaceFactory;
use Magebit\AgenticCommerce\Model\Data\DataTransferObject;
use Magento\Framework\Exception\LocalizedException;

class CompleteCheckoutSessionRequest extends DataTransferObject implements CompleteCheckoutSessionRequestInterface
{
    /**
     * @param PaymentDataInterfaceFactory $paymentDataInterfaceFactory
     * @param BuyerInterfaceFactory $buyerInterfaceFactory
     * @param array<mixed> $data
     */
    public function __construct(
        private readonly PaymentDataInterfaceFactory $paymentDataInterfaceFactory,
        private readonly BuyerInterfaceFactory $buyerInterfaceFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getBuyer(): ?BuyerInterface
    {
        $buyer = $this->getData('buyer');

        if ($buyer instanceof BuyerInterface) {
            return $buyer;
        }
        if (is_array($buyer)) {
            return $this->buyerInterfaceFactory->create(['data' => $buyer]);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentData(): PaymentDataInterface
    {
        $paymentData = $this->getData('payment_data');

        if (!is_array($paymentData)) {
            throw new LocalizedException(__('Payment data is required'));
        }

        return $this->paymentDataInterfaceFactory->create(['data' => $paymentData]);
    }
}
