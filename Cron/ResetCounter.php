<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Cron;

use Learning\CustomOrderNumber\Helper\Data;
use Learning\CustomOrderNumber\Model\Config\Source\ResetFrequency;
use Learning\CustomOrderNumber\Model\Counter;
use Learning\CustomOrderNumber\Model\ResourceModel\Counter\CollectionFactory;
use Learning\CustomOrderNumber\Model\ResourceModel\Counter as CounterResource;
use Psr\Log\LoggerInterface;

/**
 * Cron job to reset counters based on date changes
 */
class ResetCounter
{
    /**
     * ResetCounter constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param CounterResource $counterResource
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly CounterResource $counterResource,
        private readonly Data $helper,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Execute cron job to check and reset counters
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $collection = $this->collectionFactory->create();

            // Process all entity types: order, invoice, shipment, creditmemo
            foreach ($collection as $counter) {
                $storeId = $counter->getStoreId();
                $entityType = $counter->getEntityType();

                if (!$this->helper->isEnabled($storeId)) {
                    continue;
                }

                // Get reset frequency based on entity type
                $resetFrequency = $this->getResetFrequencyForEntity($entityType, $storeId);

                if ($resetFrequency === ResetFrequency::RESET_NO) {
                    continue;
                }

                // Skip entities that use "Same as Order Number" (they don't have their own counter)
                if ($this->shouldSkipEntity($entityType, $storeId)) {
                    continue;
                }

                $shouldReset = $this->shouldResetCounter($counter, $resetFrequency);

                if ($shouldReset) {
                    $this->resetCounterValue($counter, $entityType, $storeId);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'Error in counter reset cron: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }

    /**
     * Check if counter should be reset
     *
     * @param Counter $counter
     * @param string $resetFrequency
     * @return bool
     */
    private function shouldResetCounter(Counter $counter, string $resetFrequency): bool
    {
        $lastResetDate = $counter->getLastResetDate();

        if (!$lastResetDate) {
            return false;
        }

        $currentDate = date('Y-m-d');

        switch ($resetFrequency) {
            case ResetFrequency::RESET_DAILY:
                return $lastResetDate !== $currentDate;

            case ResetFrequency::RESET_MONTHLY:
                $lastMonth = date('Y-m', strtotime($lastResetDate));
                $currentMonth = date('Y-m');
                return $lastMonth !== $currentMonth;

            case ResetFrequency::RESET_YEARLY:
                $lastYear = date('Y', strtotime($lastResetDate));
                $currentYear = date('Y');
                return $lastYear !== $currentYear;

            default:
                return false;
        }
    }

    /**
     * Get reset frequency for entity type
     *
     * @param string $entityType
     * @param int $storeId
     * @return string
     */
    private function getResetFrequencyForEntity(string $entityType, int $storeId): string
    {
        switch ($entityType) {
            case Counter::ENTITY_TYPE_INVOICE:
                return $this->helper->getInvoiceResetFrequency($storeId);

            case Counter::ENTITY_TYPE_SHIPMENT:
                return $this->helper->getShipmentResetFrequency($storeId);

            case Counter::ENTITY_TYPE_CREDITMEMO:
                return $this->helper->getCreditmemoResetFrequency($storeId);

            case Counter::ENTITY_TYPE_ORDER:
            default:
                return $this->helper->getResetFrequency($storeId);
        }
    }

    /**
     * Check if entity should be skipped (when using "Same as Order Number")
     *
     * @param string $entityType
     * @param int $storeId
     * @return bool
     */
    private function shouldSkipEntity(string $entityType, int $storeId): bool
    {
        switch ($entityType) {
            case Counter::ENTITY_TYPE_INVOICE:
                return $this->helper->isInvoiceSameAsOrder($storeId);

            case Counter::ENTITY_TYPE_SHIPMENT:
                return $this->helper->isShipmentSameAsOrder($storeId);

            case Counter::ENTITY_TYPE_CREDITMEMO:
                return $this->helper->isCreditmemoSameAsOrder($storeId);

            default:
                return false;
        }
    }

    /**
     * Reset counter value to start counter
     *
     * @param Counter $counter
     * @param string $entityType
     * @param int $storeId
     * @return void
     */
    private function resetCounterValue(Counter $counter, string $entityType, int $storeId): void
    {
        try {
            // Get start counter based on entity type
            $startCounter = $this->getStartCounterForEntity($entityType, $storeId);

            $counter->setCounterValue($startCounter);
            $counter->setLastResetDate(date('Y-m-d'));

            $this->counterResource->save($counter);

            $this->logger->info(
                sprintf(
                    'Counter reset for store %d, entity type: %s, new counter: %d',
                    $storeId,
                    $entityType,
                    $startCounter
                )
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf(
                    'Failed to reset counter for store %d, entity type %s: %s',
                    $storeId,
                    $entityType,
                    $e->getMessage()
                ),
                ['exception' => $e]
            );
        }
    }

    /**
     * Get start counter for entity type
     *
     * @param string $entityType
     * @param int $storeId
     * @return int
     */
    private function getStartCounterForEntity(string $entityType, int $storeId): int
    {
        switch ($entityType) {
            case Counter::ENTITY_TYPE_INVOICE:
                return $this->helper->getInvoiceStartCounter($storeId);

            case Counter::ENTITY_TYPE_SHIPMENT:
                return $this->helper->getShipmentStartCounter($storeId);

            case Counter::ENTITY_TYPE_CREDITMEMO:
                return $this->helper->getCreditmemoStartCounter($storeId);

            case Counter::ENTITY_TYPE_ORDER:
            default:
                return $this->helper->getStartCounter($storeId);
        }
    }
}
