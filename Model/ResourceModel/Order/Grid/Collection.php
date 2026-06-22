<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Model\ResourceModel\Order\Grid;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\ResourceModel\Order;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;
use Psr\Log\LoggerInterface as Logger;

/**
 * Sales order grid collection extended to include customer_number via EAV join
 */
class Collection extends OrderGridCollection
{
    private const CUSTOMER_NUMBER_FIELD = 'customer_number';

    private const JOIN_ALIAS            = 'ecinternet_cn';

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var bool
     */
    private $joinAdded = false;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Magento\Eav\Model\Config                                    $eavConfig
     * @param string                                                       $mainTable
     * @param string                                                       $resourceModel
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface|null    $timeZone
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        EavConfig $eavConfig,
        $mainTable = 'sales_order_grid',
        $resourceModel = Order::class,
        ?TimezoneInterface $timeZone = null
    ) {
        $this->eavConfig = $eavConfig;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel, $timeZone);
    }

    /**
     * Add customer_number JOIN before filters are rendered so the column is available for filtering.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _renderFiltersBefore()
    {
        $this->addCustomerNumberJoin();

        parent::_renderFiltersBefore();
    }

    /**
     * Map the 'customer_number' filter field to the joined table column.
     *
     * @param array|string      $field
     * @param array|string|null $condition
     *
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === self::CUSTOMER_NUMBER_FIELD) {
            $this->addCustomerNumberJoin();
            $field = self::JOIN_ALIAS . '.value';
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Join customer_entity_varchar to make customer_number available in the grid SELECT.
     */
    private function addCustomerNumberJoin(): void
    {
        if ($this->joinAdded) {
            return;
        }

        try {
            $attribute = $this->eavConfig->getAttribute('customer', self::CUSTOMER_NUMBER_FIELD);
        } catch (LocalizedException) {
            return;
        }

        if (!$attribute || !$attribute->getId()) {
            return;
        }

        $this->getSelect()->joinLeft(
            [self::JOIN_ALIAS => $this->getTable('customer_entity_varchar')],
            sprintf(
                '%s.entity_id = main_table.customer_id AND %s.attribute_id = %d',
                self::JOIN_ALIAS,
                self::JOIN_ALIAS,
                (int)$attribute->getId()
            ),
            [self::CUSTOMER_NUMBER_FIELD => self::JOIN_ALIAS . '.value']
        );

        $this->joinAdded = true;
    }
}
