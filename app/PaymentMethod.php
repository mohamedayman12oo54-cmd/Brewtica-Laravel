<?php

namespace App;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case WALLET = 'wallet';

    public function label(): string
    {
        return match($this){
            self::CASH => 'cash',
            self::CREDIT_CARD => 'credit_card',
            self::DEBIT_CARD => 'debit_card',
            self::WALLET => 'wallet',
        };
    }

    public static function values(): array
    {
        return array_map(fn(PaymentMethod $method) => $method->value, self::cases());
    }
}
