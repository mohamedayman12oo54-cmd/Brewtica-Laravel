<?php

namespace App;

enum DeliveryStatus: string
{
    case ASSIGNED = 'assigned';
    case PICKED_UP = 'picked_up';
    case ON_THE_WAY = 'on_the_way';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';

    public function label(): string
    {
        return match($this){
            self::ASSIGNED => 'assigned',
            self::PICKED_UP => 'picked_up',
            self::ON_THE_WAY => 'on_the_way',
            self::DELIVERED => 'delivered',
            self::FAILED => 'failed',
        };
    }

    public static function values(): array
    {
        return array_map(fn(DeliveryStatus $status) => $status->value, self::cases());
    }
}
