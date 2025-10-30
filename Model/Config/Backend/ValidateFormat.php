<?php
declare(strict_types=1);

namespace Learning\CustomOrderNumber\Model\Config\Backend;

use Learning\CustomOrderNumber\Helper\Data;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Backend model for validating order number format
 */
class ValidateFormat extends Value
{
    /**
     * ValidateFormat constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Data $helper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private readonly Data $helper,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = [],
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Validate format before saving
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = (string) $this->getValue();

        if (!empty($value) && !$this->helper->isValidFormat($value)) {
            throw new LocalizedException(
                __(
                    'Invalid order number format. The format must contain {counter} variable and only valid variables: {counter}, {yyyy}, {yy}, {mm}, {dd}'
                )
            );
        }

        return parent::beforeSave();
    }
}
