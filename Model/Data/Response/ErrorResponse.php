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

use Magebit\AgenticCommerce\Api\Data\Response\ErrorResponseInterface;
use Magebit\AgenticCommerce\Model\Data\DataTransferObject;

/**
 * Error Response Data Transfer Object
 */
class ErrorResponse extends DataTransferObject implements ErrorResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->getDataString('type');
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
        return $this->getDataString('code');
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
        return $this->getDataString('message');
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
        return $this->getDataStringOrNull('param');
    }

    /**
     * @inheritDoc
     */
    public function setParam(?string $param): ErrorResponseInterface
    {
        return $this->setData('param', $param);
    }

    /**
     * @inheritDoc
     */
    public function getSupportedVersions(): ?array
    {
        $data = $this->getData(ErrorResponseInterface::KEY_SUPPORTED_VERSIONS);

        if (!is_array($data)) {
            return null;
        }

        return array_values(array_filter($data, 'is_string'));
    }

    /**
     * @inheritDoc
     */
    public function setSupportedVersions(?array $supportedVersions): ErrorResponseInterface
    {
        return $this->setData(ErrorResponseInterface::KEY_SUPPORTED_VERSIONS, $supportedVersions);
    }
}
