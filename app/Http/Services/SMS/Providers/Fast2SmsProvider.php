<?php

namespace App\Http\Services\SMS\Providers;

use App\Http\Services\SMS\SMSProviderInterface;

class Fast2SmsProvider implements SMSProviderInterface
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $message): bool
    {
        try {
            if (str_starts_with($to, '+91')) {
                $to = substr($to, 3);
            }

            $fields = [
                "sender_id" => $this->config['sender_id'],
                "message"   => $message,
                "language"  => $this->config['language'],
                "route"     => $this->config['route'],
                "numbers"   => $to,
            ];

            $curl = curl_init("https://www.fast2sms.com/dev/bulk");
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($fields),
                CURLOPT_HTTPHEADER => [
                    "authorization: " . $this->config['auth_key'],
                    "content-type: application/json",
                ],
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            curl_exec($curl);
            curl_close($curl);

            return true;

        } catch (\Exception $e) {
            logStore("Fast2SMS Failed", $e->getMessage());
            return false;
        }
    }
}
