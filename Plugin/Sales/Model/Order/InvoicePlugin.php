<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Plugin\Sales\Model\Order;

use Learning\CustomOrderNumber\Helper\Data;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Psr\Log\LoggerInterface;

/**
 * Plugin to set invoice increment ID same as order when configured
 */
class InvoicePlugin
{
    /**
     * InvoicePlugin constructor
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
     * Intercept before save to set invoice increment ID same as order if configured
     *
     * @param InvoiceResource $subject
     * @param AbstractModel $invoice
     * @return array
     */
    public function beforeSave(InvoiceResource $subject, AbstractModel $invoice): array
    {
        if (!($invoice instanceof Invoice)) {
            return [$invoice];
        }

        // Only process new invoices
        if ($invoice->getId()) {
            return [$invoice];
        }

        $storeId = (int) $invoice->getStoreId();

        if (!$this->helper->isEnabled($storeId)) {
            return [$invoice];
        }

        // Only set if invoice should use same number as order
        if (!$this->helper->isInvoiceSameAsOrder($storeId)) {
            return [$invoice];
        }

        // Get order and set invoice increment ID to match (with suffix for partial invoices)
        $order = $invoice->getOrder();
        if ($order && $order->getIncrementId()) {
            $orderIncrementId = $order->getIncrementId();

            // Check how many invoices already exist for this order (with increment IDs set)
            $existingInvoices = $order->getInvoiceCollection();
            $existingInvoicesCount = 0;

            foreach ($existingInvoices as $existingInvoice) {
                if ($existingInvoice->getIncrementId()) {
                    $existingInvoicesCount++;
                }
            }

            // For the first invoice, use order number as-is
            // For subsequent invoices, append a suffix (-1, -2, -3, etc.)
            if ($existingInvoicesCount > 0) {
                $invoiceIncrementId = $orderIncrementId . '-' . $existingInvoicesCount;
            } else {
                $invoiceIncrementId = $orderIncrementId;
            }

            $this->logger->info(
                'CustomOrderNumber: Setting invoice increment ID same as order (beforeSave)',
                [
                    'order_increment_id' => $orderIncrementId,
                    'invoice_increment_id' => $invoiceIncrementId,
                    'existing_invoices_count' => $existingInvoicesCount,
                    'store_id' => $storeId,
                ]
            );

            // Set the increment ID directly before Magento generates one
            $invoice->setIncrementId($invoiceIncrementId);
        }

        return [$invoice];
    }
}

