<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Domains\Documentation\Models\PageGuide;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class HelpPanelComposer
{
    public function compose(View $view): void
    {
        $routeName = Route::currentRouteName();

        if (! $routeName) {
            return;
        }

        try {
            $guide = PageGuide::where('route_name', $routeName)
                ->where('is_active', true)
                ->first();
        } catch (\Throwable) {
            $guide = null;
        }

        $view->with('helpGuide', $guide);
    }
}
