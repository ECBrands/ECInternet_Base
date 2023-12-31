<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Plugin\Sales\Api;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use ECInternet\Base\Logger\Logger;

/**
 * Plugin for Magento\Sales\Api\OrderRepositoryInterface
 */
class OrderRepositoryInterfacePlugin
{
    const FIELD_NAME = 'ship_to_id';

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $_addressRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    private $_orderExtensionFactory;

    /**
     * @var \Magento\Sales\Api\OrderAddressRepositoryInterface
     */
    private $_orderAddressRepository;

    /**
     * @var \ECInternet\Base\Logger\Logger
     */
    private $_logger;

    /**
     * OrderRepositoryInterfacePlugin constructor.
     *
     * @param \Magento\Customer\Api\AddressRepositoryInterface   $addressRepository
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory      $orderExtensionFactory
     * @param \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepository
     * @param \ECInternet\Base\Logger\Logger                     $logger
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        OrderExtensionFactory $orderExtensionFactory,
        OrderAddressRepositoryInterface $orderAddressRepository,
        Logger $logger
    ) {
        $this->_addressRepository      = $addressRepository;
        $this->_orderExtensionFactory  = $orderExtensionFactory;
        $this->_orderAddressRepository = $orderAddressRepository;
        $this->_logger                 = $logger;
    }

    /**
     * Add extension attributes to Order data object to make them accessible in API data of Order record
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface      $resultOrder
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterGet(
        /** @noinspection PhpUnusedParameterInspection */ OrderRepositoryInterface $subject,
        OrderInterface $resultOrder
    ) {
        return $this->setOrderExtensionAttributes($resultOrder);
    }

    /**
     * Add extension attributes to order data object to make them accessible in API data of all order list
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface        $subject
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult
     *
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function afterGetList(
        /** @noinspection PhpUnusedParameterInspection */ OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResult
    ) {
        /** @var \Magento\Sales\Api\Data\OrderInterface[] $orders */
        $orders = $searchResult->getItems();
        foreach ($orders as $order) {
            $this->setOrderExtensionAttributes($order);
        }

        return $searchResult;
    }

    /**
     * Set ExtensionAttributes on Order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    private function setOrderExtensionAttributes(
        OrderInterface $order
    ) {
        /** @var \Magento\Sales\Api\Data\OrderExtensionInterface|null $extensionAttributes */
        $extensionAttributes = $order->getExtensionAttributes();

        /** @var \Magento\Sales\Api\Data\OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ?: $this->_orderExtensionFactory->create();

        if ($shippingAddressId = $order->getData('shipping_address_id')) {
            /** @var \Magento\Sales\Model\Order\Address $orderAddress */
            if ($orderAddress = $this->getOrderAddress((int)$shippingAddressId)) {
                if ($customerAddressId = $orderAddress->getCustomerAddressId()) {
                    /** @var \Magento\Customer\Api\Data\AddressInterface|null $shippingAddress */
                    /** @noinspection PhpCastIsUnnecessaryInspection */
                    if ($shippingAddress = $this->getAddress((int)$customerAddressId)) {
                        /** @var \Magento\Framework\Api\AttributeInterface|null $shipToId */
                        if ($shipToId = $shippingAddress->getCustomAttribute(self::FIELD_NAME)) {
                            $orderExtension->setShipToId($shipToId->getValue());
                        }
                    }
                }
            }
        }

        // Update Order ExtensionAttributes
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }

    /**
     * Retrieve OrderAddressInterface using OrderAddressRepository.
     *
     * @param int $orderAddressId
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface|null
     */
    private function getOrderAddress(int $orderAddressId)
    {
        try {
            return $this->_orderAddressRepository->get($orderAddressId);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (NoSuchEntityException $e) {
            $this->log("Unable to get OrderAddress [$orderAddressId]. - {$e->getMessage()}.");
        }

        return null;
    }

    /**
     * Retrieve AddressInterface using AddressRepository.
     *
     * @param int $addressId
     *
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    private function getAddress(int $addressId)
    {
        try {
            return $this->_addressRepository->getById($addressId);
        } catch (LocalizedException $e) {
            $this->log('getAddress()', ['addressId' => $addressId, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Write to extension log.
     *
     * @param string $message
     * @param array  $extra
     */
    private function log(string $message, array $extra = [])
    {
        $this->_logger->info('Plugin/Sales/Api/OrderRepositoryInterfacePlugin - ' . $message, $extra);
    }
}
