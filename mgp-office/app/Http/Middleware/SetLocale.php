<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['fr', 'ha', 'zr', 'tq', 'kr', 'ff'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        App::setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // 1. Préférence de l'utilisateur connecté
        if ($user = $request->user()) {
            if ($user->locale && in_array($user->locale, self::SUPPORTED_LOCALES, strict: true)) {
                return $user->locale;
            }
        }

        // 2. Session
        $sessionLocale = $request->session()->get('locale');
        if ($sessionLocale && in_array($sessionLocale, self::SUPPORTED_LOCALES, strict: true)) {
            return $sessionLocale;
        }

        // 3. Default
        return config('app.locale', 'fr');
    }
}
