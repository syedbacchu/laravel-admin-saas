<?php

namespace App\Enums;

enum FileDestinationEnum: string
{
    case USER_IMAGE_PATH = "uploads/user";
    case GENERAL_IMAGE_PATH = "uploads";
    case SETTINGS_IMAGE_PATH = "uploads/settings";
}
