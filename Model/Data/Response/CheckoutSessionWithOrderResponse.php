<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Response;

use Magebit\AcpSpec\Api\AgenticCheckout\OrderInterface;
use Magebit\AgenticCommerce\Api\Data\Response\CheckoutSessionWithOrderResponseInterface;

/**
 * Checkout Session With Order Response Data Transfer Object
 */
class CheckoutSessionWithOrderResponse extends CheckoutSessionResponse implements CheckoutSessionWithOrderResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getOrder(): OrderInterface
    {
        $order = $this->getDataOf('order', OrderInterface::class);

        return $order ?? throw new \UnexpectedValueException('The checkout session carries no order.');
    }

    /**
     * @inheritDoc
     */
    public function setOrder(OrderInterface $order): CheckoutSessionWithOrderResponseInterface
    {
        return $this->setData('order', $order);
    }
}
