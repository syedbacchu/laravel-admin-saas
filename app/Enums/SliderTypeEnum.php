<?php

namespace App\Enums;

enum SliderTypeEnum: int
{
    case APP = 1;
    case WEB = 2;

    public static function getSliderTypeArray(): array
    {
        return [
            self::APP->value => 'APP',
            self::WEB->value => 'WEB',
        ];
    }
}
