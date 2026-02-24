<?php
namespace App\Enums;

enum GenderEnum: string
{
    case MALE = 'Male';
    case FEMALE = 'Female';
    case OTHERS = 'Others';
    public static function getGenderArray(): array
    {
        return [
            self::MALE->value => 'Male',
            self::FEMALE->value => 'Female',
            self::OTHERS->value => 'Others',
        ];
    }

}
