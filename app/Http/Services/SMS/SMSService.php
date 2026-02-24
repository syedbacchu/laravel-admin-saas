<?php

namespace App\Http\Services\SMS;

class SMSService
{
    protected SMSProviderInterface $provider;

    public function __construct(SMSManager $manager)
    {
        $this->provider = $manager->make();
    }

    public function sendOtp(string $phone, string $message): bool
    {
        if (app()->environment('local')) {
            return true; // Skip in local
        }

        return $this->provider->send($phone, $message);
    }
}
