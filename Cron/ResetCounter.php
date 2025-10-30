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
            $collection->addFieldToFilter('entity_type', Counter::ENTITY_TYPE_ORDER);

            foreach ($collection as $counter) {
                $storeId = $counter->getStoreId();
                $resetFrequency = $this->helper->getResetFrequency($storeId);

                if ($resetFrequency === ResetFrequency::RESET_NO) {
                    continue;
                }

                if (!$this->helper->isEnabled($storeId)) {
                    continue;
                }

                $shouldReset = $this->shouldResetCounter($counter, $resetFrequency);

                if ($shouldReset) {
                    $this->resetCounterValue($counter, $storeId);
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
     * Reset counter value to start counter
     *
     * @param Counter $counter
     * @param int $storeId
     * @return void
     */
    private function resetCounterValue(Counter $counter, int $storeId): void
    {
        try {
            $startCounter = $this->helper->getStartCounter($storeId);
            $counter->setCounterValue($startCounter);
            $counter->setLastResetDate(date('Y-m-d'));

            $this->counterResource->save($counter);

            $this->logger->info(
                sprintf(
                    'Counter reset for store %d, entity type: %s',
                    $storeId,
                    $counter->getEntityType()
                )
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf(
                    'Failed to reset counter for store %d: %s',
                    $storeId,
                    $e->getMessage()
                ),
                ['exception' => $e]
            );
        }
    }
}
