<?php

namespace App\Http\Services\Mail;

interface MailerInterface
{
    public function send(string $template, array $data, string $to, string $name, string $subject): bool;

    public function sendTest(string $template, array $data, string $to, string $name, string $subject): array;
}
