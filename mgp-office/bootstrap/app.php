<?php

use App\Http\Middleware\AuditLogMiddleware;
use App\Http\Middleware\Enforce2FA;
use App\Http\Middleware\EnforceVbgAccess;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware global pour toutes les requêtes web
        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Middleware pour toutes les mutations (web + api)
        $middleware->append(AuditLogMiddleware::class);

        // Les webhooks entrants (VBG, ODK, SMS) sont authentifiés par signature HMAC,
        // pas par jeton CSRF de session → exclusion du contrôle CSRF.
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);

        // Aliases pour utilisation dans les routes
        $middleware->alias([
            'vbg.access'  => EnforceVbgAccess::class,
            'enforce.2fa' => Enforce2FA::class,
            'set.locale'  => SetLocale::class,
            'audit.log'   => AuditLogMiddleware::class,
            // Spatie Permission aliases
            'role'        => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'  => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
