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

interface ErrorResponseInterface
{
    public const TYPE_INVALID_REQUEST = 'invalid_request';
    public const TYPE_REQUEST_NOT_IDEMPOTENT = 'request_not_idempotent';
    public const TYPE_PROCESSING_ERROR = 'processing_error';
    public const TYPE_SERVICE_UNAVAILABLE = 'service_unavailable';

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self;

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function setCode(string $code): self;

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self;

    /**
     * Get param (RFC 9535 JSONPath)
     *
     * @return string|null
     */
    public function getParam(): ?string;

    /**
     * Set param (RFC 9535 JSONPath)
     *
     * @param string|null $param
     * @return $this
     */
    public function setParam(?string $param): self;
}
