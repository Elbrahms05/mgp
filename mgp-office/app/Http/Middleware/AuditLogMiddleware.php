<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\Activity;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /** Routes à ne pas logger (assets, healthcheck, etc.) */
    private const SKIP_ROUTES = [
        'debugbar.*',
        'horizon.*',
        'telescope.*',
        'livewire.*',
    ];

    /** Méthodes qui ne nécessitent pas d'audit (lectures courantes) */
    private const SKIP_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ne logger que les mutations (POST, PUT, PATCH, DELETE)
        if (in_array($request->method(), self::SKIP_METHODS, strict: true)) {
            return $response;
        }

        foreach (self::SKIP_ROUTES as $pattern) {
            if ($request->routeIs($pattern)) {
                return $response;
            }
        }

        $user = $request->user();
        if (! $user) {
            return $response;
        }

        activity()
            ->causedBy($user)
            ->withProperties([
                'method'     => $request->method(),
                'url'        => $request->fullUrl(),
                'route'      => $request->route()?->getName(),
                'ip'         => $request->ip(),
                'status'     => $response->getStatusCode(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('http_request');

        return $response;
    }
}
