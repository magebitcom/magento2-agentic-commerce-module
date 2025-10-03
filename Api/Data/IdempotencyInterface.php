<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data;

/**
 * Interface for Idempotency Model
 * Provides methods to manage idempotency records for request deduplication
 */
interface IdempotencyInterface
{
    public const ENTITY_ID = 'entity_id';
    public const KEY = 'key';
    public const REQUEST_HASH = 'requestHash';
    public const RESPONSE = 'response';
    public const STATUS = 'status';
    public const EXPIRES_AT = 'expires_at';
    public const CREATED_AT = 'created_at';

    /**
     * Get key
     *
     * @return string|null
     */
    public function getKey(): ?string;

    /**
     * Set key
     *
     * @param string $key
     * @return $this
     */
    public function setKey(string $key): self;

    /**
     * Get request hash
     *
     * @return string|null
     */
    public function getRequestHash(): ?string;

    /**
     * Set request hash
     *
     * @param string $requestHash
     * @return $this
     */
    public function setRequestHash(string $requestHash): self;

    /**
     * Get response
     *
     * @return string|null
     */
    public function getResponse(): ?string;

    /**
     * Set response
     *
     * @param string $response
     * @return $this
     */
    public function setResponse(string $response): self;

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus(): ?int;

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self;

    /**
     * Get expires at
     *
     * @return string|null
     */
    public function getExpiresAt(): ?string;

    /**
     * Set expires at
     *
     * @param string $expiresAt
     * @return $this
     */
    public function setExpiresAt(string $expiresAt): self;

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;
}
