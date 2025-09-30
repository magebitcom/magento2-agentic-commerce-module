<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Api\Data\Spec;

interface ComplianceInterface
{
    public const WARNING = 'warning';
    public const WARNING_URL = 'warning_url';
    public const AGE_RESTRICTION = 'age_restriction';

    /**
     * Get the warning
     *
     * @return string|null
     */
    public function getWarning(): ?string;

    /**
     * Get the warning URL
     *
     * @return string|null
     */
    public function getWarningUrl(): ?string;

    /**
     * Get the age restriction
     *
     * @return int|null
     */
    public function getAgeRestriction(): ?int;
}

