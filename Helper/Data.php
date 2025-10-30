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
    private const XML_PATH_FORMAT = 'learning_custom_order_number/order/format';
    private const XML_PATH_START_COUNTER = 'learning_custom_order_number/order/start_counter';
    private const XML_PATH_INCREMENT_STEP = 'learning_custom_order_number/order/increment_step';
    private const XML_PATH_PADDING = 'learning_custom_order_number/order/padding';
    private const XML_PATH_RESET_COUNTER = 'learning_custom_order_number/order/reset_counter';

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
            self::XML_PATH_FORMAT,
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
            self::XML_PATH_START_COUNTER,
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
            self::XML_PATH_INCREMENT_STEP,
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
            self::XML_PATH_PADDING,
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
            self::XML_PATH_RESET_COUNTER,
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
