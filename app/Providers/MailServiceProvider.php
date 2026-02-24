<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Services\Mail\MailManager;
use App\Http\Services\Mail\MailerInterface;

class MailServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(MailManager::class, function () {
            return new MailManager();
        });

        $this->app->bind(MailerInterface::class, function () {
            return app(MailManager::class)->make();
        });
    }
}
