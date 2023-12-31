<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DayOfWeek implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => 'Sunday',    'value' => 'sun'],
            ['label' => 'Monday',    'value' => 'mon'],
            ['label' => 'Tuesday',   'value' => 'tue'],
            ['label' => 'Wednesday', 'value' => 'wed'],
            ['label' => 'Thursday',  'value' => 'thu'],
            ['label' => 'Friday',    'value' => 'fri'],
            ['label' => 'Saturday',  'value' => 'sat']
        ];
    }
}
