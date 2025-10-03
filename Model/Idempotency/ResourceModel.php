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
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Idempotency Resource Model
 */
class ResourceModel extends AbstractDb
{
    private const TABLE_NAME = 'magebit_ac_idempotency';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, IdempotencyInterface::ENTITY_ID);
    }
}
