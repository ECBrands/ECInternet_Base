<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Setup\Patch\Data;

use Magento\Customer\Model\ResourceModel\Attribute as AttributeResource;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddCustomerNumberAttributeToCustomer implements DataPatchInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute
     */
    private $attributeResource;

    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $setup;

    /**
     * AddCustomerNumberAttributeToCustomer constructor.
     *
     * @param \Magento\Customer\Model\ResourceModel\Attribute   $attributeResource
     * @param \Magento\Customer\Setup\CustomerSetupFactory      $customerSetupFactory
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    public function __construct(
        AttributeResource $attributeResource,
        CustomerSetupFactory $customerSetupFactory,
        ModuleDataSetupInterface $setup,
    ) {
        $this->attributeResource    = $attributeResource;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setup                = $setup;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $this->setup->getConnection()->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->setup]);
        $customerSetup->addAttribute('customer', 'customer_number', [
            'type'         => 'varchar',
            'label'        => 'Customer Number',
            'input'        => 'text',
            'required'     => false,
            'visible'      => true,
            'user_defined' => false,
            'position'     => 999,
            'system'       => 0,
            'is_unique'    => true,
            'backend'      => \ECInternet\Base\Model\Attribute\Backend\CustomerNumber::class,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'customer_number');
        $attribute->setData('used_in_forms', ['adminhtml_customer']);
        $attribute->setData('is_unique', 1);
        $attribute->setData('is_used_in_grid', 1);
        $attribute->setData('is_visible_in_grid', 1);
        $attribute->setData('is_filterable_in_grid', 1);
        $attribute->setData('is_searchable_in_grid', 1);

        $this->attributeResource->save($attribute);

        $this->setup->getConnection()->endSetup();
    }
}
