<?php

namespace App\Http\Services\SMS;

use App\Models\OtpConfiguration;
use App\Http\Services\SMS\Providers\{
    NexmoProvider,
    TwilioProvider,
    SslWirelessProvider,
    Fast2SmsProvider
};

class SMSManager
{
    public function make(): SMSProviderInterface
    {
        $provider = settings("sms_provider")      // DB setting recommended
            ?? env("SMS_PROVIDER", "nexmo"); // fallback

        return match ($provider) {
            'nexmo'      => new NexmoProvider([
                'api_key'    => settings('nexmo_key'),
                'api_secret' => settings('nexmo_secret'),
            ]),

            'twilio'     => new TwilioProvider([
                'sid'   => settings('twilio_sid'),
                'token' => settings('twilio_token'),
                'from'  => settings('twilio_from'),
            ]),

            'ssl'        => new SslWirelessProvider([
                'token' => settings('ssl_token'),
                'sid'   => settings('ssl_sid'),
                'url'   => settings('ssl_url'),
            ]),

            'fast2sms'   => new Fast2SmsProvider([
                'auth_key'  => settings('fast2sms_auth'),
                'sender_id' => settings('fast2sms_sender_id'),
                'route'     => settings('fast2sms_route'),
                'language'  => settings('fast2sms_language')
            ]),

            default => throw new \Exception("Invalid SMS Provider")
        };
    }
}
