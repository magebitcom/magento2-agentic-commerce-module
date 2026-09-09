<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Exception;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

/**
 * A feed id that names no feed this store serves.
 */
class FeedNotFoundException extends NoSuchEntityException
{
    /**
     * @param string $feedId
     */
    public function __construct(string $feedId)
    {
        parent::__construct(new Phrase('Feed not found: %1.', [$feedId]));
    }
}
