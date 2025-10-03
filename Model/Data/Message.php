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

use Magebit\AgenticCommerce\Api\Data\MessageInterface;

/**
 * Message Data Transfer Object
 */
class Message extends DataTransferObject implements MessageInterface
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        $type = $this->getData('type');
        return is_string($type) ? $type : '';
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): MessageInterface
    {
        return $this->setData('type', $type);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): ?string
    {
        $code = $this->getData('code');
        return is_string($code) ? $code : null;
    }

    /**
     * @inheritDoc
     */
    public function setCode(?string $code): MessageInterface
    {
        return $this->setData('code', $code);
    }

    /**
     * @inheritDoc
     */
    public function getParam(): ?string
    {
        $param = $this->getData('param');
        return is_string($param) ? $param : null;
    }

    /**
     * @inheritDoc
     */
    public function setParam(?string $param): MessageInterface
    {
        return $this->setData('param', $param);
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        $contentType = $this->getData('content_type');
        return is_string($contentType) ? $contentType : self::CONTENT_TYPE_PLAIN;
    }

    /**
     * @inheritDoc
     */
    public function setContentType(string $contentType): MessageInterface
    {
        return $this->setData('content_type', $contentType);
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        $content = $this->getData('content');
        return is_string($content) ? $content : '';
    }

    /**
     * @inheritDoc
     */
    public function setContent(string $content): MessageInterface
    {
        return $this->setData('content', $content);
    }
}
