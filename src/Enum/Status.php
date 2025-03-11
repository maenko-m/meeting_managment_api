<?php

namespace App\Enum;

enum Status: string
{
    case ACTIVE = 'активный';
    case INACTIVE = 'неактивный';

    public static function getArray(): array
    {
        return array_combine(
            array_map(fn (self $status) => $status->value, self::cases()),
            array_map(fn (self $status) => $status, self::cases())
        );
    }

    public static function getValidValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}