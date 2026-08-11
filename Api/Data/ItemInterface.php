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

use Magebit\AcpSpec\Api\AgenticCheckout\ItemInterface as SpecItemInterface;

/**
 * The spec's `Item` plus the `quantity` every upstream example sends. `Item` is
 * `additionalProperties: false` and does not declare it, which is the defect recorded in the
 * acp-php-spec README — accepted leniently here rather than rejected.
 */
interface ItemInterface extends SpecItemInterface
{
    /**
     * @return int
     */
    public function getQuantity(): int;

    /**
     * @param int $quantity
     * @return self
     */
    public function setQuantity(int $quantity): self;
}
