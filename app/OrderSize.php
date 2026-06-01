<?php

namespace App;

enum OrderSize: string
{
    case SMALL = 'small';
    case MEDIUM = 'medium';
    case LARGE = 'large';

    public function label(): string
    {
        return match($this){
            self::SMALL => 'small',
            self::MEDIUM => 'medium',
            self::LARGE => 'large',
        };
    }

    public static function values(): array
    {
        return array_map(fn(OrderSize $size) => $size->value, self::cases());
    }
}
