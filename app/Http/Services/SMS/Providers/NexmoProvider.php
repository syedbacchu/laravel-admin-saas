<?php

namespace App\Http\Services\SMS\Providers;

use App\Http\Services\SMS\SMSProviderInterface;

class NexmoProvider implements SMSProviderInterface
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $message): bool
    {
        try {
            $params = [
                "api_key"       => $this->config['api_key'],
                "api_secret"    => $this->config['api_secret'],
                "request_type"  => 'SINGLE_SMS',
                "message_type"  => 'UNICODE',
                "mobile"        => $to,
                "message_body"  => $message
            ];

            $curl = curl_init("https://portal.adnsms.com/api/v1/secure/send-sms");
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($params),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            curl_exec($curl);
            curl_close($curl);

            return true;

        } catch (\Exception $e) {
            logStore("Nexmo SMS Failed", $e->getMessage());
            return false;
        }
    }
}
