<?php
return [
    'default' => env('MAIL_PROVIDER', 'smtp'),

    'providers' => [
        'smtp' => [
            'driver'        => env('MAIL_MAILER', 'smtp'),
            'host'          => env('MAIL_HOST'),
            'port'          => env('MAIL_PORT'),
            'username'      => env('MAIL_USERNAME'),
            'password'      => env('MAIL_PASSWORD'),
            'encryption'    => env('MAIL_ENCRYPTION'),
            'from_address'  => env('MAIL_FROM_ADDRESS'),
            'from_name'     => env('MAIL_FROM_NAME', env('APP_NAME')),
        ],
    ],
];
