<?php

namespace App;

enum UserGender: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    public function label(): string
    {
        return match($this){
            self::MALE => 'male',
            self::FEMALE => 'female',
        };
    }
}
