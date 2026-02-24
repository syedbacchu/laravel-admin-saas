<?php

namespace App\Http\Services\Mail;

use Illuminate\Support\Facades\Mail;
use Exception;

class MailService implements MailerInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        // Dynamically override mail configuration
        config([
            'mail.default'                   => $config['driver'],
            'mail.mailers.smtp.host'         => $config['host'],
            'mail.mailers.smtp.port'         => $config['port'],
            'mail.mailers.smtp.username'     => $config['username'],
            'mail.mailers.smtp.password'     => $config['password'],
            'mail.mailers.smtp.encryption'   => $config['encryption'],
            'mail.from.address'              => $config['from_address'],
            'mail.from.name'                 => $config['from_name'],
        ]);
    }

    public function send(string $template, array $data, string $to, string $name, string $subject): bool
    {
        try {
            Mail::send($template, $data, function ($message) use ($to, $name, $subject) {
                $message->to($to, $name)
                    ->subject($subject)
                    ->from($this->config['from_address'], $this->config['from_name']);
            });

            return true;

        } catch (Exception $e) {
            logStore('mail send error', $e->getMessage());
            return false;
        }
    }

    public function sendTest(string $template, array $data, string $to, string $name, string $subject): array
    {
        try {
            Mail::send($template, $data, function ($message) use ($to, $name, $subject) {
                $message->to($to, $name)
                    ->subject($subject)
                    ->from($this->config['from_address'], $this->config['from_name']);
            });

            return ['success' => true, 'message' => 'Mail configuration is working'];

        } catch (Exception $e) {
            logStore('test mail send error', $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
