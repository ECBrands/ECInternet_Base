<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Backend model for 'customer_number' attribute
 * Added in 1.3.3
 */
class CustomerNumber extends AbstractBackend
{
    /**
     * Make 'customer_number' unique before save
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function beforeSave($object)
    {
        $this->checkUniqueCustomerNumber($object);

        return parent::beforeSave($object);
    }

    /**
     * Confirm unique 'customer_number' value
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function checkUniqueCustomerNumber(DataObject $object)
    {
        $attribute = $this->getAttribute();
        $entity    = $attribute->getEntity();
        $value     = $object->getData($attribute->getAttributeCode());

        // This value will be trim()'d, so it cannot be null
        if ($value !== null) {
            if (!$entity->checkAttributeUniqueValue($attribute, $object)) {
                throw new CouldNotSaveException(__("Customer has non-unique 'customer_number' value"));
            }
        }
    }
}
