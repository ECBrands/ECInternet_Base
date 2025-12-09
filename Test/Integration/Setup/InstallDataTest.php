<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Test\Integration\Setup;

use Magento\Eav\Api\AttributeRepositoryInterface;
use PHPUnit\Framework\TestCase;

class InstallDataTest extends TestCase
{
    private $objectManager;
    private $attributeRepository;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->attributeRepository = $this->objectManager->create(AttributeRepositoryInterface::class);
    }

    public function testIfCustomerNumberAttributeWasCreatedCorrectly()
    {
        $attribute = $this->attributeRepository->get('customer', 'customer_number');

        $this->assertNotNull($attribute);
        $this->assertEquals('customer_number', $attribute->getAttributeCode());
        $this->assertEquals('Customer Number', $attribute->getDefaultFrontendLabel());
    }
}
