<?php

return [

    /*
    |--------------------------------------------------------------------------
    | File Uploader Requirements
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'ALLOWED_IMAGE_TYPE' => env('ALLOWED_IMAGE_TYPE') ? env('ALLOWED_IMAGE_TYPE') : [],
    'MAX_UPLOAD_IMAGE_SIZE' => env('MAX_UPLOAD_IMAGE_SIZE') ? env('MAX_UPLOAD_IMAGE_SIZE') : 2048, // in KB
    'DEFAULT_IMAGE_FORMAT' => env('DEFAULT_IMAGE_FORMAT') ? env('DEFAULT_IMAGE_FORMAT') : 'webp',
    'DEFAULT_IMAGE_QUALITY' => env('DEFAULT_IMAGE_QUALITY') ? env('DEFAULT_IMAGE_QUALITY') : 80,
    'AWS_ACCESS_KEY_ID' => env('AWS_ACCESS_KEY_ID') ? env('AWS_ACCESS_KEY_ID') : "",
    'AWS_SECRET_ACCESS_KEY' => env('AWS_SECRET_ACCESS_KEY') ? env('AWS_SECRET_ACCESS_KEY') : "",
    'AWS_DEFAULT_REGION' => env('AWS_DEFAULT_REGION') ? env('AWS_DEFAULT_REGION') : "",
    'AWS_BUCKET' => env('AWS_BUCKET') ? env('AWS_BUCKET') : "",
    'AWS_URL' => env('AWS_URL') ? env('AWS_URL') : "",
];
