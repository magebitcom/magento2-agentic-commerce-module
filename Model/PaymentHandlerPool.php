<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model;

use Magebit\AgenticCommerce\Api\PaymentHandlerInterface;
use Magebit\AgenticCommerce\Api\Data\PaymentDataInterface;
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
        if (!isset($this->handlers[$paymentData->getProvider()])) {
            throw new \InvalidArgumentException(sprintf('Payment handler for method %s not found', $paymentData->getProvider()));
        }

        return $this->handlers[$paymentData->getProvider()]->handle($cart, $paymentData);
    }
}
