<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data;

use Magebit\AgenticCommerce\Api\Data\OrderInterface;
use Magento\Framework\DataObject;

/**
 * Order Data Transfer Object
 */
class Order extends DataObject implements OrderInterface
{
    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return (string) $this->getData('id');
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id): OrderInterface
    {
        return $this->setData('id', $id);
    }

    /**
     * @inheritDoc
     */
    public function getCheckoutSessionId(): string
    {
        return (string) $this->getData('checkout_session_id');
    }

    /**
     * @inheritDoc
     */
    public function setCheckoutSessionId(string $checkoutSessionId): OrderInterface
    {
        return $this->setData('checkout_session_id', $checkoutSessionId);
    }

    /**
     * @inheritDoc
     */
    public function getPermalinkUrl(): string
    {
        return (string) $this->getData('permalink_url');
    }

    /**
     * @inheritDoc
     */
    public function setPermalinkUrl(string $url): OrderInterface
    {
        return $this->setData('permalink_url', $url);
    }
}
