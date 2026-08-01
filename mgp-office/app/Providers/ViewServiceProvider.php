<?php

declare(strict_types=1);

namespace App\Providers;

use App\View\Composers\HelpPanelComposer;
use App\View\Composers\LocaleComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', HelpPanelComposer::class);
        View::composer('*', LocaleComposer::class);
    }
}
