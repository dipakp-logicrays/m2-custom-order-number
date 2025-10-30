<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Counter model for managing order number counters
 */
class Counter extends AbstractModel
{
    public const ENTITY_TYPE_ORDER = 'order';
    public const ENTITY_TYPE_INVOICE = 'invoice';
    public const ENTITY_TYPE_SHIPMENT = 'shipment';
    public const ENTITY_TYPE_CREDITMEMO = 'creditmemo';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Counter::class);
    }

    /**
     * Get entity type
     *
     * @return string|null
     */
    public function getEntityType(): ?string
    {
        return $this->getData('entity_type');
    }

    /**
     * Set entity type
     *
     * @param string $entityType
     * @return $this
     */
    public function setEntityType(string $entityType): self
    {
        return $this->setData('entity_type', $entityType);
    }

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return (int) $this->getData('store_id');
    }

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId(int $storeId): self
    {
        return $this->setData('store_id', $storeId);
    }

    /**
     * Get counter value
     *
     * @return int
     */
    public function getCounterValue(): int
    {
        return (int) $this->getData('counter_value');
    }

    /**
     * Set counter value
     *
     * @param int $counterValue
     * @return $this
     */
    public function setCounterValue(int $counterValue): self
    {
        return $this->setData('counter_value', $counterValue);
    }

    /**
     * Get last reset date
     *
     * @return string|null
     */
    public function getLastResetDate(): ?string
    {
        return $this->getData('last_reset_date');
    }

    /**
     * Set last reset date
     *
     * @param string|null $date
     * @return $this
     */
    public function setLastResetDate(?string $date): self
    {
        return $this->setData('last_reset_date', $date);
    }
}
