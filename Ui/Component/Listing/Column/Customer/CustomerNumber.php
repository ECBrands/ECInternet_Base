<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Ui\Component\Listing\Column\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Exception;

/**
 * CustomerNumber Column
 */
class CustomerNumber extends Column
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $_customerRepository;

    /**
     * CustomerNumber constructor.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface            $customerRepository
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory
     * @param array                                                        $components
     * @param array                                                        $data
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->_customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                // Extract customer_number
                $customerNumber = $this->getCustomerNumber((int)$item['entity_id']);

                // Assign to item
                $item[$this->getData('name')] = $customerNumber;
            }
        }

        return $dataSource;
    }

    /**
     * Retrieve 'customer_number' attribute value from Customer using 'customer_id'.
     *
     * @param int $customerId
     *
     * @return string
     */
    private function getCustomerNumber(
        int $customerId
    ) {
        if ($customer = $this->getCustomer($customerId)) {
            if ($customerNumber = $customer->getCustomAttribute('customer_number')) {
                return $customerNumber->getValue();
            }
        }

        return '';
    }

    /**
     * Retrieve Customer using CustomerRepository.
     *
     * @param int $customerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    private function getCustomer(int $customerId)
    {
        try {
            return $this->_customerRepository->getById($customerId);
        } catch (Exception $e) {
            error_log("Unable to lookup customer by id: {$e->getMessage()}");
        }

        return null;
    }
}
