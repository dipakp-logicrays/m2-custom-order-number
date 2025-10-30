<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Reset frequency options for counter reset configuration
 */
class ResetFrequency implements OptionSourceInterface
{
    public const RESET_NO = 'no';
    public const RESET_DAILY = 'daily';
    public const RESET_MONTHLY = 'monthly';
    public const RESET_YEARLY = 'yearly';

    /**
     * Get reset frequency options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::RESET_NO, 'label' => __('No')],
            ['value' => self::RESET_DAILY, 'label' => __('Daily')],
            ['value' => self::RESET_MONTHLY, 'label' => __('Monthly')],
            ['value' => self::RESET_YEARLY, 'label' => __('Yearly')],
        ];
    }
}
