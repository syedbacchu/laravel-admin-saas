<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pattern and storage path settings
    |--------------------------------------------------------------------------
    |
    | The env key for pattern and storage path with a default value
    |
    */
    'log_viewer_author' => env('LOGVIEWER_AUTHOR', 'SdTech'),
    'log_viewer_title' => env('LOGVIEWER_APP_TITLE', 'LARAVEL'),
    'max_file_size' => env('LOGVIEWER_MAX_FILE_SIZE', 52428800), // size in Byte
    'pattern'       => env('LOGVIEWER_PATTERN', '*.log'),
    'storage_path'  => env('LOGVIEWER_STORAGE_PATH', storage_path('logs')),
];
