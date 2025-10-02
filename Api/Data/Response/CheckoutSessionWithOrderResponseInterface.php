<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data\Response;

use Magebit\AgenticCommerce\Api\Data\OrderInterface;

interface CheckoutSessionWithOrderResponseInterface extends CheckoutSessionResponseInterface
{
    /**
     * Get order
     *
     * @return \Magebit\AgenticCommerce\Api\Data\OrderInterface
     */
    public function getOrder(): OrderInterface;

    /**
     * Set order
     *
     * @param \Magebit\AgenticCommerce\Api\Data\OrderInterface $order
     * @return $this
     */
    public function setOrder(OrderInterface $order): self;
}
