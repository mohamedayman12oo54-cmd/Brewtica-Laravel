<?php

namespace App;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case OUT_OF_DELIVERY = 'out_of_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this){
            self::PENDING => 'pending',
            self::PREPARING => 'preparing',
            self::READY => 'ready',
            self::OUT_OF_DELIVERY => 'out_of_delivery',
            self::DELIVERED => 'delivered',
            self::CANCELLED => 'cancelled',
        };
    }

    public static function values(): array
    {
        return array_map(fn(OrderStatus $status) => $status->value, self::cases());
    }
}
