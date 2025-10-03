<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Idempotency;

use Magebit\AgenticCommerce\Api\Data\IdempotencyInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Idempotency Model
 */
class Model extends AbstractModel implements IdempotencyInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getKey(): ?string
    {
        $value = $this->getData(self::KEY);
        return $value !== null ? (string) $value : null;
    }

    /**
     * @inheritDoc
     */
    public function setKey(string $key): IdempotencyInterface
    {
        return $this->setData(self::KEY, $key);
    }

    /**
     * @inheritDoc
     */
    public function getRequestHash(): ?string
    {
        $value = $this->getData(self::REQUEST_HASH);
        return $value !== null ? (string) $value : null;
    }

    /**
     * @inheritDoc
     */
    public function setRequestHash(string $requestHash): IdempotencyInterface
    {
        return $this->setData(self::REQUEST_HASH, $requestHash);
    }

    /**
     * @inheritDoc
     */
    public function getResponse(): ?string
    {
        $value = $this->getData(self::RESPONSE);
        return $value !== null ? (string) $value : null;
    }

    /**
     * @inheritDoc
     */
    public function setResponse(string $response): IdempotencyInterface
    {
        return $this->setData(self::RESPONSE, $response);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): ?int
    {
        $value = $this->getData(self::STATUS);
        return $value !== null && $value !== '' ? (int) $value : null;
    }

    /**
     * @inheritDoc
     */
    public function setStatus(int $status): IdempotencyInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getExpiresAt(): ?string
    {
        $value = $this->getData(self::EXPIRES_AT);
        return $value !== null ? (string) $value : null;
    }

    /**
     * @inheritDoc
     */
    public function setExpiresAt(string $expiresAt): IdempotencyInterface
    {
        return $this->setData(self::EXPIRES_AT, $expiresAt);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        $value = $this->getData(self::CREATED_AT);
        return $value !== null ? (string) $value : null;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): IdempotencyInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
