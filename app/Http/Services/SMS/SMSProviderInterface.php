<?php

namespace App\Http\Services\SMS;

interface SMSProviderInterface
{
    public function send(string $to, string $message): bool;
}
