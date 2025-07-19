<?php

namespace App\Domain\Helper;

use DateInterval;

class HDate
{
    public static function calculateInterval(string|float|int $interval): DateInterval
    {
        if (is_int($interval) || is_float($interval) || is_string($interval) && ctype_digit($interval)) {
            $interval = (int)$interval;
            return new DateInterval("PT{$interval}S");
        }

        if (str_starts_with($interval, 'P')) {
            return new DateInterval($interval);
        }

        return DateInterval::createFromDateString($interval);
    }
}
