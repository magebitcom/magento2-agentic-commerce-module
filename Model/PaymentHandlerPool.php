<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model;

use Magebit\AgenticCommerce\Api\PaymentHandlerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentDataInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;

class PaymentHandlerPool
{
    /**
     * @param PaymentHandlerInterface[] $handlers
     */
    public function __construct(
        private readonly array $handlers = []
    ) {
    }

    /**
     * @param CartInterface $cart
     * @param PaymentDataInterface $paymentData
     * @return PaymentInterface
     */
    public function get(CartInterface $cart, PaymentDataInterface $paymentData): PaymentInterface
    {
        if (!isset($this->handlers[$paymentData->getHandlerId()])) {
            throw new \InvalidArgumentException(sprintf('Payment handler %s not found', $paymentData->getHandlerId()));
        }

        return $this->handlers[$paymentData->getHandlerId()]->handle($cart, $paymentData);
    }
}
