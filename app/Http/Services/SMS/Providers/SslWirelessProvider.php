<?php

namespace App\Http\Services\SMS\Providers;

use App\Http\Services\SMS\SMSProviderInterface;

class SslWirelessProvider implements SMSProviderInterface
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
                "api_token" => $this->config['token'],
                "sid"       => $this->config['sid'],
                "msisdn"    => $to,
                "sms"       => $message,
                "csms_id"   => uniqid()
            ];

            $curl = curl_init($this->config['url']);
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
            logStore("SSL Wireless SMS Failed", $e->getMessage());
            return false;
        }
    }
}
