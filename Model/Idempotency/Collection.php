<?php

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Idempotency;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Idempotency Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Initialize collection model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
