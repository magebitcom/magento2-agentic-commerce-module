<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Feed\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\ComplianceInterface;

trait WithComplianceData
{
    /**
     * Get the warning
     *
     * @return string|null
     */
    public function getWarning(): ?string
    {
        return $this->getData(ComplianceInterface::WARNING);
    }

    /**
     * Get the warning URL
     *
     * @return string|null
     */
    public function getWarningUrl(): ?string
    {
        return $this->getData(ComplianceInterface::WARNING_URL);
    }

    /**
     * Get the age restriction
     *
     * @return int|null
     */
    public function getAgeRestriction(): ?int
    {
        return $this->getData(ComplianceInterface::AGE_RESTRICTION);
    }
}
