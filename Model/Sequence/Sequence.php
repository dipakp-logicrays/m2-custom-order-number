<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Model\Sequence;

use Learning\CustomOrderNumber\Model\CounterService;
use Magento\Framework\DB\Sequence\SequenceInterface;
use Psr\Log\LoggerInterface;

/**
 * Custom sequence implementation for order numbers
 */
class Sequence implements SequenceInterface
{
    /**
     * Sequence constructor
     *
     * @param CounterService $counterService
     * @param string $entityType
     * @param int $storeId
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CounterService $counterService,
        private readonly string $entityType,
        private readonly int $storeId,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Retrieve current value
     *
     * @return string
     */
    public function getCurrentValue(): string
    {
        // Not used in our implementation
        return '';
    }

    /**
     * Retrieve next value
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNextValue(): string
    {
        $this->logger->info(
            'CustomOrderNumber Sequence: getNextValue() called',
            [
                'entity_type' => $this->entityType,
                'store_id' => $this->storeId,
            ]
        );

        try {
            // Generate number based on entity type
            if ($this->entityType === 'invoice') {
                $number = $this->counterService->getNextInvoiceNumber($this->storeId);
            } elseif ($this->entityType === 'shipment') {
                $number = $this->counterService->getNextShipmentNumber($this->storeId);
            } else {
                $number = $this->counterService->getNextOrderNumber($this->storeId);
            }

            $this->logger->info(
                'CustomOrderNumber Sequence: Generated number',
                [
                    'entity_type' => $this->entityType,
                    'number' => $number,
                    'store_id' => $this->storeId,
                ]
            );

            return $number;
        } catch (\Exception $e) {
            $this->logger->error(
                'CustomOrderNumber Sequence: Failed to generate number',
                [
                    'entity_type' => $this->entityType,
                    'exception' => $e->getMessage(),
                    'store_id' => $this->storeId,
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }
}

