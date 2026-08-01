<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Complaint\Models\Complaint;
use App\Observers\Complaint\ComplaintObserver;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(ViewServiceProvider::class);
    }

    public function boot(): void
    {
        if (app()->environment('production') || str_contains(request()->getHost(), 'ngrok')) {
            URL::forceScheme('https');
        }

        // Register custom SMS notification channel
        Notification::extend('sms', function () {
            return new SmsChannel();
        });

        Complaint::observe(ComplaintObserver::class);
    }
}
