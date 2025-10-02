<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data;

/**
 * Message interface for info and error messages in checkout sessions
 *
 * Supports two message types:
 * - Info messages: informational messages to the buyer
 * - Error messages: validation or processing errors with specific error codes
 */
interface MessageInterface
{
    public const TYPE_INFO = 'info';
    public const TYPE_ERROR = 'error';

    public const CODE_MISSING = 'missing';
    public const CODE_INVALID = 'invalid';
    public const CODE_OUT_OF_STOCK = 'out_of_stock';
    public const CODE_PAYMENT_DECLINED = 'payment_declined';
    public const CODE_REQUIRES_SIGN_IN = 'requires_sign_in';
    public const CODE_REQUIRES_3DS = 'requires_3ds';

    public const CONTENT_TYPE_PLAIN = 'plain';
    public const CONTENT_TYPE_MARKDOWN = 'markdown';

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
     * Get code (only for error messages)
     *
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * Set code (only for error messages)
     *
     * @param string|null $code
     * @return $this
     */
    public function setCode(?string $code): self;

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

    /**
     * Get content type
     *
     * @return string
     */
    public function getContentType(): string;

    /**
     * Set content type
     *
     * @param string $contentType
     * @return $this
     */
    public function setContentType(string $contentType): self;

    /**
     * Get content
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Set content
     *
     * @param string $content
     * @return $this
     */
    public function setContent(string $content): self;
}
