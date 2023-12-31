<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Api;

interface SyncCompleteInterface
{
    /**
     * Sync has completed
     *
     * @param string $syncType
     *
     * @return void
     */
    public function syncComplete(string $syncType);
}
