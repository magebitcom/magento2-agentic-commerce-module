<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Response;

use Magebit\AgenticCommerce\Api\Data\OrderInterface;
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
        return $this->getData('order');
    }

    /**
     * @inheritDoc
     */
    public function setOrder(OrderInterface $order): CheckoutSessionWithOrderResponseInterface
    {
        return $this->setData('order', $order);
    }
}
