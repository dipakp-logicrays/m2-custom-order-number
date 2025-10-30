<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Plugin\Sales\Model\Order;

use Learning\CustomOrderNumber\Helper\Data;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo as CreditmemoResource;
use Psr\Log\LoggerInterface;

class CreditmemoPlugin
{
    public function __construct(
        private readonly Data $helper,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Set credit memo increment ID same as order when configured, with suffix for multiples
     *
     * @param CreditmemoResource $subject
     * @param Creditmemo $creditmemo
     * @return array
     */
    public function beforeSave(
        CreditmemoResource $subject,
        Creditmemo $creditmemo,
    ): array {
        // Only process new credit memos
        if ($creditmemo->getId()) {
            return [$creditmemo];
        }

        $storeId = (int) $creditmemo->getStoreId();

        if (!$this->helper->isEnabled($storeId)) {
            return [$creditmemo];
        }

        if (!$this->helper->isCreditmemoSameAsOrder($storeId)) {
            return [$creditmemo];
        }

        $order = $creditmemo->getOrder();
        if ($order && $order->getIncrementId()) {
            $orderIncrementId = $order->getIncrementId();

            // Count existing credit memos with increment IDs
            $existingCreditmemos = $order->getCreditmemosCollection();
            $existingCount = 0;
            foreach ($existingCreditmemos as $existing) {
                if ($existing->getIncrementId()) {
                    $existingCount++;
                }
            }

            $creditmemoIncrementId = $existingCount > 0
                ? $orderIncrementId . '-' . $existingCount
                : $orderIncrementId;

            $creditmemo->setIncrementId($creditmemoIncrementId);

            $this->logger->info(
                'CustomOrderNumber CreditmemoPlugin: Set credit memo increment ID same as order (beforeSave)',
                [
                    'order_increment_id' => $orderIncrementId,
                    'creditmemo_increment_id' => $creditmemoIncrementId,
                    'existing_creditmemos_count' => $existingCount,
                    'store_id' => $storeId,
                ]
            );
        }

        return [$creditmemo];
    }
}
