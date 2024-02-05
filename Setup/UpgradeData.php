<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use ECInternet\Base\Model\Attribute\Backend\CustomerNumber;

/**
 * Data upgrade script
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $_customerSetupFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $_eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    private $_attributeSetFactory;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * UpgradeData constructor.
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory   $customerSetupFactory
     * @param \Magento\Eav\Model\Config                      $eavConfig
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory
     * @param \Magento\Eav\Setup\EavSetupFactory             $eavSetupFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        Config $eavConfig,
        AttributeSetFactory $attributeSetFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->_customerSetupFactory = $customerSetupFactory;
        $this->_eavConfig            = $eavConfig;
        $this->_attributeSetFactory  = $attributeSetFactory;
        $this->_eavSetupFactory      = $eavSetupFactory;
    }

    /**
     * Upgrades DB for a module
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface   $context
     *
     * @return void
     * @throws \Exception
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
            $customerSetup = $this->_customerSetupFactory->create(['setup' => $setup]);

            /** @var \Magento\Eav\Model\Entity\Type $customerEntity */
            $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
            $attributeSet     = $this->_attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
            $attribute = $customerSetup->getEavConfig()
                ->getAttribute(Customer::ENTITY, 'customer_number')
                ->addData([
                    'attribute_set_id'   => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId
                ]);

            /** @noinspection PhpDeprecationInspection */
            $attribute->save();
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                'customer_address',
                'ship_to_id',
                [
                    'type'         => 'varchar',
                    'label'        => 'Ship-To Id',
                    'input'        => 'text',
                    'required'     => false,
                    'visible'      => true,
                    'user_defined' => false,
                    'position'     => 999,
                    'system'       => 0
                ]
            );

            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $customerAddressAttribute */
            $customerAddressAttribute = $this->_eavConfig->getAttribute(
                'customer_address',
                'ship_to_id'
            );

            $customerAddressAttribute->setData(
                'used_in_forms',
                ['adminhtml_customer_address']
            );

            /** @noinspection PhpDeprecationInspection */
            $customerAddressAttribute->save();

            /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
            $customerSetup = $this->_customerSetupFactory->create(['setup' => $setup]);

            /** @var \Magento\Eav\Model\Entity\Type $customerAddressEntity */
            $customerAddressEntity = $customerSetup->getEavConfig()->getEntityType('customer_address');
            $attributeSetId        = $customerAddressEntity->getDefaultAttributeSetId();

            /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
            $attributeSet     = $this->_attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $attribute = $customerSetup->getEavConfig()
                ->getAttribute('customer_address', 'ship_to_id')
                ->addData([
                    'attribute_set_id'   => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId
                ]);

            /** @noinspection PhpDeprecationInspection */
            $attribute->save();
        }

        // Add 'customer_number' attribute to admin Customer form
        if (version_compare($context->getVersion(), '1.2.3', '<')) {
            $attribute = $this->_eavConfig->getAttribute(Customer::ENTITY, 'customer_number')
                ->addData([
                    'used_in_forms' => [
                        'adminhtml_customer'
                    ]
                ]);

            /** @noinspection PhpDeprecationInspection */
            $attribute->save();
        }

        // Add 'ship_to_id' attribute to admin CustomerAddress form
        if (version_compare($context->getVersion(), '1.2.4', '<')) {
            $attribute = $this->_eavConfig->getAttribute('customer_address', 'ship_to_id')
                ->addData([
                    'used_in_forms' => [
                        'adminhtml_customer_address'
                    ]
                ]);

            /** @noinspection PhpDeprecationInspection */
            $attribute->save();
        }

        // Set 'customer_number' to have unique values
        if (version_compare($context->getVersion(), '1.3.3', '<')) {
            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $customerNumberAttribute */
            $customerNumberAttribute = $this->_eavConfig->getAttribute(
                Customer::ENTITY,
                'customer_number'
            );

            $customerNumberAttribute->setData('is_unique', true);
            $customerNumberAttribute->setData('backend_model', CustomerNumber::class);

            /** @noinspection PhpDeprecationInspection */
            $customerNumberAttribute->save();
        }

        if (version_compare($context->getVersion(), '1.3.5', '<')) {
            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $customerNumberAttribute */
            $customerNumberAttribute = $this->_eavConfig->getAttribute(
                Customer::ENTITY,
                'customer_number'
            );

            $customerNumberAttribute->setData('is_used_in_grid', 1);
            $customerNumberAttribute->setData('is_visible_in_grid', 1);
            $customerNumberAttribute->setData('is_filterable_in_grid', 1);
            $customerNumberAttribute->setData('is_searchable_in_grid', 1);

            /** @noinspection PhpDeprecationInspection */
            $customerNumberAttribute->save();
        }

        $installer->endSetup();
    }
}
