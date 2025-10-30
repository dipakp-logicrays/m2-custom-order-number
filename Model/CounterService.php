<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Model;

use Learning\CustomOrderNumber\Helper\Data;
use Learning\CustomOrderNumber\Model\Config\Source\ResetFrequency;
use Learning\CustomOrderNumber\Model\CounterFactory;
use Learning\CustomOrderNumber\Model\ResourceModel\Counter as CounterResource;
use Learning\CustomOrderNumber\Model\ResourceModel\Counter\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Service for managing order counters with database locking
 */
class CounterService
{
    /**
     * CounterService constructor
     *
     * @param CounterFactory $counterFactory
     * @param CounterResource $counterResource
     * @param CollectionFactory $collectionFactory
     * @param Data $helper
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CounterFactory $counterFactory,
        private readonly CounterResource $counterResource,
        private readonly CollectionFactory $collectionFactory,
        private readonly Data $helper,
        private readonly ResourceConnection $resourceConnection,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Get next order number for store
     *
     * @param int $storeId
     * @return string
     * @throws LocalizedException
     */
    public function getNextOrderNumber(int $storeId): string
    {
        return $this->getNextNumber(Counter::ENTITY_TYPE_ORDER, $storeId);
    }

    /**
     * Get next invoice number for store
     *
     * @param int $storeId
     * @param string|null $orderIncrementId
     * @return string
     * @throws LocalizedException
     */
    public function getNextInvoiceNumber(int $storeId, ?string $orderIncrementId = null): string
    {
        // Check if invoice should use same number as order
        if ($this->helper->isInvoiceSameAsOrder($storeId) && $orderIncrementId) {
            return $orderIncrementId;
        }

        return $this->getNextNumber(Counter::ENTITY_TYPE_INVOICE, $storeId);
    }

    /**
     * Get next shipment number for store
     *
     * @param int $storeId
     * @param string|null $orderIncrementId
     * @return string
     * @throws LocalizedException
     */
    public function getNextShipmentNumber(int $storeId, ?string $orderIncrementId = null): string
    {
        // Check if shipment should use same number as order
        if ($this->helper->isShipmentSameAsOrder($storeId) && $orderIncrementId) {
            return $orderIncrementId;
        }

        return $this->getNextNumber(Counter::ENTITY_TYPE_SHIPMENT, $storeId);
    }

    /**
     * Get next credit memo number for store
     *
     * @param int $storeId
     * @param string|null $orderIncrementId
     * @return string
     * @throws LocalizedException
     */
    public function getNextCreditmemoNumber(int $storeId, ?string $orderIncrementId = null): string
    {
        // Check if credit memo should use same number as order
        if ($this->helper->isCreditmemoSameAsOrder($storeId) && $orderIncrementId) {
            return $orderIncrementId;
        }

        return $this->getNextNumber(Counter::ENTITY_TYPE_CREDITMEMO, $storeId);
    }

    /**
     * Get next number for entity type and store
     *
     * @param string $entityType
     * @param int $storeId
     * @return string
     * @throws LocalizedException
     */
    private function getNextNumber(string $entityType, int $storeId): string
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('learning_custom_order_counter');

        $connection->beginTransaction();

        try {
            $select = $connection->select()
                ->from($tableName)
                ->where('entity_type = ?', $entityType)
                ->where('store_id = ?', $storeId)
                ->forUpdate(true);

            $row = $connection->fetchRow($select);

            if (!$row) {
                $counter = $this->initializeCounter($entityType, $storeId);
            } else {
                $counter = $this->counterFactory->create();
                $counter->setData($row);

                $this->checkAndResetCounter($counter, $entityType, $storeId);
            }

            $currentCounter = $counter->getCounterValue();

            // Get configuration based on entity type
            if ($entityType === Counter::ENTITY_TYPE_INVOICE) {
                $format = $this->helper->getInvoiceFormat($storeId);
                $padding = $this->helper->getInvoicePadding($storeId);
                $incrementStep = $this->helper->getInvoiceIncrementStep($storeId);
            } elseif ($entityType === Counter::ENTITY_TYPE_SHIPMENT) {
                $format = $this->helper->getShipmentFormat($storeId);
                $padding = $this->helper->getShipmentPadding($storeId);
                $incrementStep = $this->helper->getShipmentIncrementStep($storeId);
            } elseif ($entityType === Counter::ENTITY_TYPE_CREDITMEMO) {
                $format = $this->helper->getCreditmemoFormat($storeId);
                $padding = $this->helper->getCreditmemoPadding($storeId);
                $incrementStep = $this->helper->getCreditmemoIncrementStep($storeId);
            } else {
                $format = $this->helper->getFormat($storeId);
                $padding = $this->helper->getPadding($storeId);
                $incrementStep = $this->helper->getIncrementStep($storeId);
            }

            $number = $this->helper->generateOrderNumber($format, $currentCounter, $padding);

            $counter->setCounterValue($currentCounter + $incrementStep);

            $this->counterResource->save($counter);

            $connection->commit();

            return $number;
        } catch (\Exception $e) {
            $connection->rollBack();

            $this->logger->error(
                "Error generating {$entityType} number: " . $e->getMessage(),
                [
                    'exception' => $e,
                    'entity_type' => $entityType,
                ]
            );

            throw new LocalizedException(
                __("Unable to generate {$entityType} number. Please try again.")
            );
        }
    }

    /**
     * Initialize counter for new entity type and store
     *
     * @param string $entityType
     * @param int $storeId
     * @return Counter
     * @throws LocalizedException
     */
    private function initializeCounter(string $entityType, int $storeId): Counter
    {
        // Get start counter based on entity type
        if ($entityType === Counter::ENTITY_TYPE_INVOICE) {
            $startCounter = $this->helper->getInvoiceStartCounter($storeId);
        } elseif ($entityType === Counter::ENTITY_TYPE_SHIPMENT) {
            $startCounter = $this->helper->getShipmentStartCounter($storeId);
        } elseif ($entityType === Counter::ENTITY_TYPE_CREDITMEMO) {
            $startCounter = $this->helper->getCreditmemoStartCounter($storeId);
        } else {
            $startCounter = $this->helper->getStartCounter($storeId);
        }

        $counter = $this->counterFactory->create();
        $counter->setEntityType($entityType);
        $counter->setStoreId($storeId);
        $counter->setCounterValue($startCounter);
        $counter->setLastResetDate(date('Y-m-d'));

        $this->counterResource->save($counter);

        return $counter;
    }

    /**
     * Check if counter needs to be reset based on date
     *
     * @param Counter $counter
     * @param string $entityType
     * @param int $storeId
     * @return void
     */
    private function checkAndResetCounter(Counter $counter, string $entityType, int $storeId): void
    {
        // Get reset frequency based on entity type
        if ($entityType === Counter::ENTITY_TYPE_INVOICE) {
            $resetFrequency = $this->helper->getInvoiceResetFrequency($storeId);
        } elseif ($entityType === Counter::ENTITY_TYPE_SHIPMENT) {
            $resetFrequency = $this->helper->getShipmentResetFrequency($storeId);
        } elseif ($entityType === Counter::ENTITY_TYPE_CREDITMEMO) {
            $resetFrequency = $this->helper->getCreditmemoResetFrequency($storeId);
        } else {
            $resetFrequency = $this->helper->getResetFrequency($storeId);
        }

        if ($resetFrequency === ResetFrequency::RESET_NO) {
            return;
        }

        $lastResetDate = $counter->getLastResetDate();

        if (!$lastResetDate) {
            $counter->setLastResetDate(date('Y-m-d'));
            return;
        }

        $shouldReset = false;
        $currentDate = date('Y-m-d');

        switch ($resetFrequency) {
            case ResetFrequency::RESET_DAILY:
                $shouldReset = $lastResetDate !== $currentDate;
                break;

            case ResetFrequency::RESET_MONTHLY:
                $lastMonth = date('Y-m', strtotime($lastResetDate));
                $currentMonth = date('Y-m');
                $shouldReset = $lastMonth !== $currentMonth;
                break;

            case ResetFrequency::RESET_YEARLY:
                $lastYear = date('Y', strtotime($lastResetDate));
                $currentYear = date('Y');
                $shouldReset = $lastYear !== $currentYear;
                break;
        }

        if ($shouldReset) {
            // Get start counter based on entity type
            if ($entityType === Counter::ENTITY_TYPE_INVOICE) {
                $startCounter = $this->helper->getInvoiceStartCounter($storeId);
            } elseif ($entityType === Counter::ENTITY_TYPE_SHIPMENT) {
                $startCounter = $this->helper->getShipmentStartCounter($storeId);
            } elseif ($entityType === Counter::ENTITY_TYPE_CREDITMEMO) {
                $startCounter = $this->helper->getCreditmemoStartCounter($storeId);
            } else {
                $startCounter = $this->helper->getStartCounter($storeId);
            }

            $counter->setCounterValue($startCounter);
            $counter->setLastResetDate($currentDate);
        }
    }

    /**
     * Reset counter for specific store and entity type
     *
     * @param string $entityType
     * @param int $storeId
     * @return void
     * @throws LocalizedException
     */
    public function resetCounter(string $entityType, int $storeId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_type', $entityType)
            ->addFieldToFilter('store_id', $storeId);

        $counter = $collection->getFirstItem();

        if ($counter->getId()) {
            $startCounter = $this->helper->getStartCounter($storeId);
            $counter->setCounterValue($startCounter);
            $counter->setLastResetDate(date('Y-m-d'));

            $this->counterResource->save($counter);
        }
    }
}
