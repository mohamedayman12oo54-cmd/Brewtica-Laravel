<?php

namespace App;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this){
            self::PENDING => 'pending',
            self::COMPLETED => 'completed',
            self::FAILED => 'failed',
            self::REFUNDED => 'refunded',
        };
    }

    public static function values(): array
    {
        return array_map(fn(PaymentStatus $status) => $status->value, self::cases());
    }
}
