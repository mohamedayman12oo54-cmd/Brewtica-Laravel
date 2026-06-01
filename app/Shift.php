<?php

namespace App;

enum Shift: string
{
    case MORNING = 'morning';
    case EVENING = 'evening';
    case NIGHT = 'night';

    public function label(): string
    {
        return match($this){
            self::MORNING => 'morning',
            self::EVENING => 'evening',
            self::NIGHT => 'night',
        };
    }

    public static function values(): array
    {
        return array_map(fn (Shift $shift) => $shift->value, self::cases());
    }
}
