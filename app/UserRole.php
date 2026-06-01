<?php

namespace App;

enum UserRole: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';
    case DELIVERY = 'delivery';
    case STAFF = 'staff';

    public function label(): string
    {
        return match($this){
            self::ADMIN => 'admin',
            self::CUSTOMER => 'customer',
            self::DELIVERY => 'delivery',
            self::STAFF => 'staff',
        };
    }

    public static function values(): array
    {
        return array_map(fn (UserRole $role) => $role->value, self::cases());
    }
}
