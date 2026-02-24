<?php

namespace App\Http\Services\Mail;

class MailManager
{
    public function make(): MailerInterface
    {
        $settings = settings(); // fetch from DB

        $config = [
            'driver'        => $settings['mail_driver'] ?? env('MAIL_DRIVER'),
            'host'          => $settings['mail_host'] ?? env('MAIL_HOST'),
            'port'          => $settings['mail_port'] ?? env('MAIL_PORT'),
            'username'      => $settings['mail_username'] ?? env('MAIL_USERNAME'),
            'password'      => $settings['mail_password'] ?? env('MAIL_PASSWORD'),
            'encryption'    => $settings['mail_encryption'] ?? env('MAIL_ENCRYPTION'),
            'from_address'  => $settings['mail_from_address'] ?? env('MAIL_FROM_ADDRESS'),
            'from_name'     => $settings['app_title'] ?? env('MAIL_FROM_NAME'),
        ];

        return new MailService($config);
    }
}
