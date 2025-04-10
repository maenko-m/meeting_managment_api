<?php

namespace App\Enum;

enum RecurrenceType: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';

    public static function getValidValues(): array
    {
        return array_map(fn(self $type) => $type->value, self::cases());
    }
}