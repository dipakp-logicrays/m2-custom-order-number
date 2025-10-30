<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Plugin\Sales\Model;

use Learning\CustomOrderNumber\Helper\Data;
use Learning\CustomOrderNumber\Model\CounterService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Plugin to set custom increment ID for orders
 */
class OrderPlugin
{
    /**
     * OrderPlugin constructor
     *
     * @param Data $helper
     * @param CounterService $counterService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Data $helper,
        private readonly CounterService $counterService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Set custom increment ID before order is saved
     *
     * @param Order $subject
     * @param callable $proceed
     * @return Order
     * @throws LocalizedException
     */
    public function aroundSave(Order $subject, callable $proceed): Order
    {
        // Only process new orders (without entity_id)
        if (!$subject->getId()) {
            $storeId = (int) $subject->getStoreId();

            $this->logger->debug(
                'CustomOrderNumber Plugin: aroundSave called for new order',
                [
                    'order_id' => $subject->getId(),
                    'store_id' => $storeId,
                    'current_increment_id' => $subject->getIncrementId(),
                    'is_enabled' => $this->helper->isEnabled($storeId),
                ]
            );

            if ($this->helper->isEnabled($storeId)) {
                try {
                    // Generate custom increment ID before save
                    $customIncrementId = $this->counterService->getNextOrderNumber($storeId);
                    $this->logger->info(
                        'CustomOrderNumber Plugin: Setting custom increment ID',
                        [
                            'custom_increment_id' => $customIncrementId,
                            'store_id' => $storeId,
                            'old_increment_id' => $subject->getIncrementId(),
                        ]
                    );
                    $subject->setIncrementId($customIncrementId);
                } catch (\Exception $e) {
                    $this->logger->error(
                        'Failed to set custom order increment ID: ' . $e->getMessage(),
                        [
                            'exception' => $e,
                            'store_id' => $storeId,
                            'order_id' => $subject->getId(),
                            'trace' => $e->getTraceAsString(),
                        ]
                    );

                    throw new LocalizedException(
                        __('Unable to generate order number. Please contact support.')
                    );
                }
            }
        }

        // Call the original save method
        $result = $proceed();

        // Log after save to verify
        if (!$subject->getId()) {
            $this->logger->debug(
                'CustomOrderNumber Plugin: Order saved',
                [
                    'order_id' => $subject->getId(),
                    'increment_id' => $subject->getIncrementId(),
                ]
            );
        }

        return $result;
    }
}
