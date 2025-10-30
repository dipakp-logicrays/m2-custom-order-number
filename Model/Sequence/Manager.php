<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Model\Sequence;

use Learning\CustomOrderNumber\Helper\Data;
use Learning\CustomOrderNumber\Model\CounterService;
use Learning\CustomOrderNumber\Model\Counter;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceSequenceMeta;
use Magento\SalesSequence\Model\SequenceFactory;
use Psr\Log\LoggerInterface;

/**
 * Override for Sequence Manager to provide custom order numbers
 */
class Manager extends \Magento\SalesSequence\Model\Manager
{
    /**
     * Manager constructor
     *
     * @param ResourceSequenceMeta $resourceSequenceMeta
     * @param SequenceFactory $sequenceFactory
     * @param Data $helper
     * @param CounterService $counterService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceSequenceMeta $resourceSequenceMeta,
        SequenceFactory $sequenceFactory,
        private readonly Data $helper,
        private readonly CounterService $counterService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($resourceSequenceMeta, $sequenceFactory);
    }

    /**
     * Returns sequence for given entityType and store
     *
     * @param string $entityType
     * @param int $storeId
     * @return \Magento\Framework\DB\Sequence\SequenceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSequence($entityType, $storeId)
    {
        // Cast storeId to int to ensure type safety
        $storeId = (int) $storeId;

        // Only handle order entity type with custom sequence
        if ($entityType === Counter::ENTITY_TYPE_ORDER && $this->helper->isEnabled($storeId)) {
            $this->logger->info(
                'CustomOrderNumber: Using custom sequence for order',
                [
                    'entity_type' => $entityType,
                    'store_id' => $storeId,
                ]
            );

            return new Sequence($this->counterService, $storeId, $this->logger);
        }

        // Use default Magento sequence for other entity types or when disabled
        $this->logger->debug(
            'CustomOrderNumber: Using default Magento sequence',
            [
                'entity_type' => $entityType,
                'store_id' => $storeId,
                'is_enabled' => $this->helper->isEnabled($storeId),
            ]
        );

        return parent::getSequence($entityType, $storeId);
    }
}

