<?php

declare(strict_types=1);

namespace App\View\Composers;

use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class LocaleComposer
{
    private const LOCALES = [
        'fr' => 'Français',
        'ha' => 'Haoussa',
        'zr' => 'Zarma',
        'tq' => 'Tamasheq',
        'kr' => 'Kanouri',
        'ff' => 'Fulfulde',
    ];

    public function compose(View $view): void
    {
        $view->with([
            'currentLocale'     => App::getLocale(),
            'availableLocales'  => self::LOCALES,
        ]);
    }
}
