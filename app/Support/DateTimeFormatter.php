<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

class DateTimeFormatter
{
    public const DISPLAY_FORMAT = 'd M Y g:i A';

    public static function format(?DateTimeInterface $date, string $format = self::DISPLAY_FORMAT): string
    {
        if ($date === null) {
            return '—';
        }

        return Carbon::parse($date)
            ->timezone(config('app.timezone'))
            ->format($format);
    }
}
