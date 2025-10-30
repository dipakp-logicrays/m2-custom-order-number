<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Model\ResourceModel\Counter;

use Learning\CustomOrderNumber\Model\Counter;
use Learning\CustomOrderNumber\Model\ResourceModel\Counter as CounterResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Counter collection
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
        $this->_init(Counter::class, CounterResource::class);
    }
}
