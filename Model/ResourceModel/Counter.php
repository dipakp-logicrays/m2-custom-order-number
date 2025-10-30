<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Counter resource model
 */
class Counter extends AbstractDb
{
    private const TABLE_NAME = 'learning_custom_order_counter';
    private const PRIMARY_KEY = 'entity_id';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, self::PRIMARY_KEY);
    }
}
