<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Test\Integration\Setup;

use Magento\Eav\Model\Config as EavConfig;
use Magento\TestFramework\Helper\Bootstrap;
use ECInternet\Base\Model\Attribute\Backend\CustomerNumber;
use Exception;

class ExtensionInstallTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    protected function setUp(): void
    {
        $objectManager   = Bootstrap::getObjectManager();
        $this->eavConfig = $objectManager->get(EavConfig::class);
    }

    public function testCustomerAttributeWasCreatedCorrectly()
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        $attribute = $this->getAttribute('customer', 'customer_number');
        if ($attribute === null) {
            $this->fail('Customer attribute "customer_number" does not exist.');
        }

        $this->assertNotNull($attribute, 'Customer attribute "customer_number" should exist.');
        $this->assertEquals('varchar', $attribute->getBackendType(), 'Customer attribute "customer_number" should have backend type "varchar".');
        $this->assertEquals('Customer Number', $attribute->getStoreLabel(), 'Customer attribute "customer_number" should have store label "Customer Number".');
        $this->assertEquals(0, $attribute->getIsRequired(), 'Customer attribute "customer_number" should not be required.');
        $this->assertEquals(1, $attribute->getData('is_visible'), 'Customer attribute "customer_number" should be visible on frontend.');
        $this->assertEquals(0, $attribute->getIsUserDefined(), 'Customer attribute "customer_number" should not be user defined.');
        $this->assertEquals(1, $attribute->getIsUnique(), 'Customer attribute "customer_number" should be unique.');
        $this->assertEquals(999, $attribute->getData('sort_order'), 'Customer attribute "customer_number" should have position 999.');
        $this->assertEquals(CustomerNumber::class, $attribute->getBackendModel(), 'Customer attribute "customer_number" should have backend model "CustomerNumber".');
        $this->assertEquals('text', $attribute->getFrontendInput(), 'Customer attribute "customer_number" should have frontend input type "text".');

        // customer_eav_attribute
        $this->assertEquals(1, $attribute->getData('is_used_in_grid'), 'Customer attribute "customer_number" should be used in grid.');
        $this->assertEquals(1, $attribute->getData('is_visible_in_grid'), 'Customer attribute "customer_number" should be visible in grid.');
        $this->assertEquals(1, $attribute->getData('is_filterable_in_grid'), 'Customer attribute "customer_number" should be filterable in grid.');
        $this->assertEquals(1, $attribute->getData('is_searchable_in_grid'), 'Customer attribute "customer_number" should be searchable in grid.');

        $this->assertEquals(['adminhtml_customer'], $attribute->getUsedInForms(), 'Customer attribute "customer_number" should be used in adminhtml_customer form.');
    }

    public function testCustomerAddressAttributeWasCreatedCorrectly()
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        $attribute = $this->getAttribute('customer_address', 'ship_to_id');
        if ($attribute === null) {
            $this->fail('CustomerAddress attribute "ship_to_id" does not exist.');
        }

        $this->assertNotNull($attribute, 'CustomerAddress attribute "customer_number" should exist.');
        $this->assertEquals('varchar', $attribute->getBackendType(), 'CustomerAddress attribute "ship_to_id" should have backend type "varchar".');
        $this->assertEquals('Ship-To Id', $attribute->getStoreLabel(), 'CustomerAddress attribute "ship_to_id" should have store label "Ship-To Id".');
        $this->assertEquals('text', $attribute->getFrontendInput(), 'CustomerAddress attribute "ship_to_id" should have frontend input type "text".');
        $this->assertEquals(0, $attribute->getIsRequired(), 'CustomerAddress attribute "ship_to_id" should not be required.');
        $this->assertEquals(1, $attribute->getData('is_visible'), 'CustomerAddress attribute "ship_to_id" should be visible on frontend.');
        $this->assertEquals(0, $attribute->getIsUserDefined(), 'CustomerAddress attribute "ship_to_id" should not be user defined.');
        $this->assertEquals(0, $attribute->getIsUnique(), 'CustomerAddress attribute "ship_to_id" should not be unique.');
        $this->assertEquals(999, $attribute->getData('sort_order'), 'CustomerAddress attribute "ship_to_id" should have position 999.');

        $this->assertEquals(['adminhtml_customer_address'], $attribute->getUsedInForms(), 'CustomerAddress attribute "ship_to_id" should be used in adminhtml_customer_address form.');
    }

    private function getAttribute($entityTypeCode, $attributeCode)
    {
        try {
            if ($attribute = $this->eavConfig->getAttribute($entityTypeCode, $attributeCode)) {
                if ($attribute->getAttributeId()) {
                    return $attribute;
                }
            }
        } catch (Exception) {
        }

        return null;
    }
}
