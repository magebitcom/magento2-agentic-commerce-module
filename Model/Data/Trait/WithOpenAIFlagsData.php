<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\OpenAIFlagsInterface;

trait WithOpenAIFlagsData
{
    /**
     * Get the enable search
     *
     * @return bool
     */
    public function getEnableSearch(): bool
    {
        return (bool) $this->getData(OpenAIFlagsInterface::ENABLE_SEARCH);
    }

    /**
     * Get the enable checkout
     *
     * @return bool
     */
    public function getEnableCheckout(): bool
    {
        return (bool) $this->getData(OpenAIFlagsInterface::ENABLE_CHECKOUT);
    }
}
