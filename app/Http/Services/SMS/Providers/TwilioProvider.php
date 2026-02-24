<?php

namespace App\Http\Services\SMS\Providers;

use App\Http\Services\SMS\SMSProviderInterface;
use Twilio\Rest\Client;

class TwilioProvider implements SMSProviderInterface
{
    protected $sid, $token, $from;

    public function __construct($config)
    {
        $this->sid   = $config['sid'];
        $this->token = $config['token'];
        $this->from  = $config['from'];
    }

    public function send(string $to, string $message): bool
    {
        try {
            $client = new Client($this->sid, $this->token);
            $client->messages->create($to, [
                'from' => $this->from,
                'body' => $message
            ]);
            return true;

        } catch (\Exception $e) {
            logStore("Twilio SMS Failed", $e->getMessage());
            return false;
        }
    }
}
