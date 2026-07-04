<?php

namespace App\Enums;

enum UserTypeEnum: string
{
    case ADMIN = 'admin';
    case OWNER = 'owner';
    case STAFF = 'staff';

    public static function getUserTypeArray(): array
    {
        return [
            self::ADMIN->value => 'Admin',
            self::OWNER->value => 'Owner',
            self::STAFF->value => 'Staff',
        ];
    }
}
