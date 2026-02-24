<?php

namespace App\Enums;

enum UploadFolderEnum: string
{
    case GENERAL = 'uploads';
    case PROFILE = 'uploads/profile';

    public static function getUploadFolderTypeArray(): array
    {
        return [
            self::GENERAL->value => 'uploads',
            self::PROFILE->value => 'uploads/profile',
        ];
    }
}
