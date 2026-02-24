<?php
namespace App\Enums;

enum StatusEnum: int
{
    case ACTIVE = 1;
    case PENDING = 0;
    case SUSPENDED = 2;
    case TEMPORARY_DEACTIVE = 3;

    public static function getStatusArray(): array
    {
        return [
            self::ACTIVE->value => 'Active',
            self::PENDING->value => 'Pending',
            self::SUSPENDED->value => 'Suspended',
            self::TEMPORARY_DEACTIVE->value => 'Temporary Deactive',
        ];
    }

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::PENDING => 'Pending',
            self::SUSPENDED => 'Suspended',
            self::TEMPORARY_DEACTIVE => 'Temporary Deactive',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'green',
            self::PENDING => 'yellow',
            self::SUSPENDED => 'red',
            self::TEMPORARY_DEACTIVE => 'red',
        };
    }

    public static function getActiveArray(): array
    {
        return [
            self::ACTIVE->value => 'Active',
            self::PENDING->value => 'Inactive',
        ];
    }

    public static function getYesArray(): array
    {
        return [
            self::ACTIVE->value => 'Yes',
            self::PENDING->value => 'No',
        ];
    }
    public static function getDeactiveArray(): array
    {
        return [
            self::ACTIVE->value => 'Active',
            self::PENDING->value => 'Deactive',
        ];
    }
}
