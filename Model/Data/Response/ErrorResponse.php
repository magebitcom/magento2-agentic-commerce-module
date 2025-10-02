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

use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Model\Data\DataTransferObject;
use Magento\Framework\DataObject;

/**
 * Error Response Data Transfer Object
 */
class ErrorResponse extends DataObject implements ErrorResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return (string) $this->getData('type');
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): ErrorResponseInterface
    {
        return $this->setData('type', $type);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return (string) $this->getData('code');
    }

    /**
     * @inheritDoc
     */
    public function setCode(string $code): ErrorResponseInterface
    {
        return $this->setData('code', $code);
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): string
    {
        return (string) $this->getData('message');
    }

    /**
     * @inheritDoc
     */
    public function setMessage(string $message): ErrorResponseInterface
    {
        return $this->setData('message', $message);
    }

    /**
     * @inheritDoc
     */
    public function getParam(): ?string
    {
        return $this->getData('param');
    }

    /**
     * @inheritDoc
     */
    public function setParam(?string $param): ErrorResponseInterface
    {
        return $this->setData('param', $param);
    }
}
