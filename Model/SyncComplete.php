<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Model;

use Magento\Framework\Event\Manager;
use ECInternet\Base\Api\SyncCompleteInterface;

/**
 * SyncComplete Model
 */
class SyncComplete implements SyncCompleteInterface
{
    const EVENT_NAME      = 'ecinternet_sync_complete';

    const SYNC_TYPE_FIELD = 'sync_type';

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $_eventManager;

    /**
     * SyncComplete constructor.
     *
     * @param \Magento\Framework\Event\Manager $eventManager
     */
    public function __construct(
        Manager $eventManager
    ) {
        $this->_eventManager = $eventManager;
    }

    /**
     * Dispatch 'ecinternet_sync_complete' event, including 'sync_type' as additional data.
     *
     * @param string $syncType
     *
     * @return void
     */
    public function syncComplete(string $syncType)
    {
        $this->_eventManager->dispatch(
            self::EVENT_NAME,
            [
                self::SYNC_TYPE_FIELD => $syncType
            ]
        );
    }
}
