<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Ui\Component\Listing\Column;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
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
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $_orderRepository;

    /**
     * CustomerNumber constructor.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface            $customerRepository
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface                  $orderRepository
     * @param array                                                        $components
     * @param array                                                        $data
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->_customerRepository = $customerRepository;
        $this->_orderRepository    = $orderRepository;
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                /** @var \Magento\Sales\Api\Data\OrderInterface $order */
                $order = $this->_orderRepository->get($item['entity_id']);

                // Extract customer_number
                $customerNumber = $this->getCustomerNumber($order);

                // Assign to item
                $item[$this->getData('name')] = $customerNumber;
            }
        }

        return $dataSource;
    }

    /**
     * Retrieve 'customer_number' attribute value from Customer using 'customer_id' from the Order.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return string
     */
    private function getCustomerNumber(
        OrderInterface $order
    ) {
        if ($customerId = $order->getCustomerId()) {
            /** @noinspection PhpCastIsUnnecessaryInspection */
            if ($customer = $this->getCustomer((int)$customerId)) {
                if ($customerNumber = $customer->getCustomAttribute('customer_number')) {
                    return $customerNumber->getValue();
                }
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
