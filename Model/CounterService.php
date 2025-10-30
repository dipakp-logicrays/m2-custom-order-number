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
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('learning_custom_order_counter');

        $connection->beginTransaction();

        try {
            $select = $connection->select()
                ->from($tableName)
                ->where('entity_type = ?', Counter::ENTITY_TYPE_ORDER)
                ->where('store_id = ?', $storeId)
                ->forUpdate(true);

            $row = $connection->fetchRow($select);

            if (!$row) {
                $counter = $this->initializeCounter($storeId);
            } else {
                $counter = $this->counterFactory->create();
                $counter->setData($row);

                $this->checkAndResetCounter($counter, $storeId);
            }

            $currentCounter = $counter->getCounterValue();
            $format = $this->helper->getFormat($storeId);
            $padding = $this->helper->getPadding($storeId);

            $orderNumber = $this->helper->generateOrderNumber($format, $currentCounter, $padding);

            $incrementStep = $this->helper->getIncrementStep($storeId);
            $counter->setCounterValue($currentCounter + $incrementStep);

            $this->counterResource->save($counter);

            $connection->commit();

            return $orderNumber;
        } catch (\Exception $e) {
            $connection->rollBack();

            $this->logger->error(
                'Error generating order number: ' . $e->getMessage(),
                ['exception' => $e]
            );

            throw new LocalizedException(
                __('Unable to generate order number. Please try again.')
            );
        }
    }

    /**
     * Initialize counter for new store
     *
     * @param int $storeId
     * @return Counter
     * @throws LocalizedException
     */
    private function initializeCounter(int $storeId): Counter
    {
        $startCounter = $this->helper->getStartCounter($storeId);

        $counter = $this->counterFactory->create();
        $counter->setEntityType(Counter::ENTITY_TYPE_ORDER);
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
     * @param int $storeId
     * @return void
     */
    private function checkAndResetCounter(Counter $counter, int $storeId): void
    {
        $resetFrequency = $this->helper->getResetFrequency($storeId);

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
            $startCounter = $this->helper->getStartCounter($storeId);
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
