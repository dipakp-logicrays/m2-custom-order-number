<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Plugin\Sales\Model\Order;

use Learning\CustomOrderNumber\Helper\Data;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Psr\Log\LoggerInterface;

/**
 * Plugin to set shipment increment ID as order increment ID when "Same as Order Number" is enabled
 */
class ShipmentPlugin
{
    /**
     * ShipmentPlugin constructor
     *
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Data $helper,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Before save plugin to set shipment increment ID
     *
     * @param ShipmentResource $subject
     * @param Shipment $shipment
     * @return array
     */
    public function beforeSave(
        ShipmentResource $subject,
        Shipment $shipment,
    ): array {
        // Only process new shipments
        if ($shipment->getId()) {
            return [$shipment];
        }

        $storeId = (int) $shipment->getStoreId();

        if (!$this->helper->isEnabled($storeId)) {
            return [$shipment];
        }

        // Only set if shipment should use same number as order
        if (!$this->helper->isShipmentSameAsOrder($storeId)) {
            return [$shipment];
        }

        // Get order and set shipment increment ID to match (with suffix for multiple shipments)
        $order = $shipment->getOrder();

        if ($order && $order->getIncrementId()) {
            $orderIncrementId = $order->getIncrementId();

            // Check how many shipments already exist for this order (with increment IDs set)
            $existingShipments = $order->getShipmentsCollection();
            $existingShipmentsCount = 0;

            foreach ($existingShipments as $existingShipment) {
                if ($existingShipment->getIncrementId()) {
                    $existingShipmentsCount++;
                }
            }

            // For the first shipment, use order number as-is
            // For subsequent shipments, append a suffix (-1, -2, -3, etc.)
            if ($existingShipmentsCount > 0) {
                $shipmentIncrementId = $orderIncrementId . '-' . $existingShipmentsCount;
            } else {
                $shipmentIncrementId = $orderIncrementId;
            }

            $shipment->setIncrementId($shipmentIncrementId);

            $this->logger->info(
                'CustomOrderNumber ShipmentPlugin: Set shipment increment ID same as order (beforeSave)',
                [
                    'order_increment_id' => $orderIncrementId,
                    'shipment_increment_id' => $shipmentIncrementId,
                    'existing_shipments_count' => $existingShipmentsCount,
                    'store_id' => $storeId,
                ]
            );
        }

        return [$shipment];
    }
}

