<?php
namespace App\Enums;

enum VerificationCodeTypeEnum: int
{
    case EMAIL = 1;
    case PHONE = 2;
    case USERNAME = 3;

    public static function getTypeArray(): array
    {
        return [
            self::EMAIL->value => 'Email',
            self::PHONE->value => 'Phone',
            self::USERNAME->value => 'Username',
        ];
    }

    public function label(): string
    {
        return match($this) {
            self::EMAIL => 'Email',
            self::PHONE => 'Phone',
            self::USERNAME => 'Username',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::EMAIL => 'green',
            self::PHONE => 'yellow',
            self::USERNAME => 'red',
        };
    }
}
