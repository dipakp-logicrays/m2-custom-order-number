<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper for custom order number formatting
 */
class Data extends AbstractHelper
{
    private const XML_PATH_ENABLED = 'learning_custom_order_number/general/enabled';

    // Order paths
    private const XML_PATH_ORDER_FORMAT = 'learning_custom_order_number/order/format';
    private const XML_PATH_ORDER_START_COUNTER = 'learning_custom_order_number/order/start_counter';
    private const XML_PATH_ORDER_INCREMENT_STEP = 'learning_custom_order_number/order/increment_step';
    private const XML_PATH_ORDER_PADDING = 'learning_custom_order_number/order/padding';
    private const XML_PATH_ORDER_RESET_COUNTER = 'learning_custom_order_number/order/reset_counter';

    // Invoice paths
    private const XML_PATH_INVOICE_SAME_AS_ORDER = 'learning_custom_order_number/invoice/same_as_order';
    private const XML_PATH_INVOICE_FORMAT = 'learning_custom_order_number/invoice/format';
    private const XML_PATH_INVOICE_START_COUNTER = 'learning_custom_order_number/invoice/start_counter';
    private const XML_PATH_INVOICE_INCREMENT_STEP = 'learning_custom_order_number/invoice/increment_step';
    private const XML_PATH_INVOICE_PADDING = 'learning_custom_order_number/invoice/padding';
    private const XML_PATH_INVOICE_RESET_COUNTER = 'learning_custom_order_number/invoice/reset_counter';

    // Shipment paths
    private const XML_PATH_SHIPMENT_SAME_AS_ORDER = 'learning_custom_order_number/shipment/same_as_order';
    private const XML_PATH_SHIPMENT_FORMAT = 'learning_custom_order_number/shipment/format';
    private const XML_PATH_SHIPMENT_START_COUNTER = 'learning_custom_order_number/shipment/start_counter';
    private const XML_PATH_SHIPMENT_INCREMENT_STEP = 'learning_custom_order_number/shipment/increment_step';
    private const XML_PATH_SHIPMENT_PADDING = 'learning_custom_order_number/shipment/padding';
    private const XML_PATH_SHIPMENT_RESET_COUNTER = 'learning_custom_order_number/shipment/reset_counter';

    /**
     * Data constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
    ) {
        parent::__construct($context);
    }

    /**
     * Check if module is enabled for specific store
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get order number format
     *
     * @param int|null $storeId
     * @return string
     */
    public function getFormat(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_FORMAT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get start counter value
     *
     * @param int|null $storeId
     * @return int
     */
    public function getStartCounter(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_START_COUNTER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get increment step
     *
     * @param int|null $storeId
     * @return int
     */
    public function getIncrementStep(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_INCREMENT_STEP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get counter padding
     *
     * @param int|null $storeId
     * @return int
     */
    public function getPadding(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_PADDING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get reset counter frequency
     *
     * @param int|null $storeId
     * @return string
     */
    public function getResetFrequency(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_RESET_COUNTER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if invoice should use same format as order
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isInvoiceSameAsOrder(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_INVOICE_SAME_AS_ORDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get invoice number format
     *
     * @param int|null $storeId
     * @return string
     */
    public function getInvoiceFormat(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_INVOICE_FORMAT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get invoice start counter value
     *
     * @param int|null $storeId
     * @return int
     */
    public function getInvoiceStartCounter(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_INVOICE_START_COUNTER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get invoice increment step
     *
     * @param int|null $storeId
     * @return int
     */
    public function getInvoiceIncrementStep(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_INVOICE_INCREMENT_STEP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get invoice counter padding
     *
     * @param int|null $storeId
     * @return int
     */
    public function getInvoicePadding(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_INVOICE_PADDING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get invoice reset counter frequency
     *
     * @param int|null $storeId
     * @return string
     */
    public function getInvoiceResetFrequency(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_INVOICE_RESET_COUNTER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if shipment should use same format as order
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isShipmentSameAsOrder(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHIPMENT_SAME_AS_ORDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get shipment number format
     *
     * @param int|null $storeId
     * @return string
     */
    public function getShipmentFormat(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_SHIPMENT_FORMAT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get shipment start counter value
     *
     * @param int|null $storeId
     * @return int
     */
    public function getShipmentStartCounter(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_SHIPMENT_START_COUNTER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get shipment increment step
     *
     * @param int|null $storeId
     * @return int
     */
    public function getShipmentIncrementStep(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_SHIPMENT_INCREMENT_STEP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get shipment counter padding
     *
     * @param int|null $storeId
     * @return int
     */
    public function getShipmentPadding(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_SHIPMENT_PADDING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get shipment reset counter frequency
     *
     * @param int|null $storeId
     * @return string
     */
    public function getShipmentResetFrequency(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_SHIPMENT_RESET_COUNTER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Generate order number from format and counter
     *
     * @param string $format
     * @param int $counter
     * @param int $padding
     * @return string
     */
    public function generateOrderNumber(string $format, int $counter, int $padding): string
    {
        $replacements = [
            '{counter}' => $this->formatCounter($counter, $padding),
            '{yyyy}' => date('Y'),
            '{yy}' => date('y'),
            '{mm}' => date('m'),
            '{dd}' => date('d'),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $format
        );
    }

    /**
     * Format counter with padding
     *
     * @param int $counter
     * @param int $padding
     * @return string
     */
    private function formatCounter(int $counter, int $padding): string
    {
        if ($padding > 0) {
            return str_pad((string) $counter, $padding, '0', STR_PAD_LEFT);
        }

        return (string) $counter;
    }

    /**
     * Validate format pattern
     *
     * @param string $format
     * @return bool
     */
    public function isValidFormat(string $format): bool
    {
        if (empty($format)) {
            return false;
        }

        if (strpos($format, '{counter}') === false) {
            return false;
        }

        $validVariables = ['{counter}', '{yyyy}', '{yy}', '{mm}', '{dd}'];
        $pattern = '/{[^}]+}/';

        preg_match_all($pattern, $format, $matches);

        foreach ($matches[0] as $variable) {
            if (!in_array($variable, $validVariables, true)) {
                return false;
            }
        }

        return true;
    }
}
