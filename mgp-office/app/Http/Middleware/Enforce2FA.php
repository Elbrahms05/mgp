<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Settings\SecuritySettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Enforce2FA
{
    public function __construct(private readonly SecuritySettings $securitySettings) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $requiredRoles = $this->securitySettings->two_factor_required_roles;

        $userRequires2FA = false;
        foreach ($requiredRoles as $role) {
            if ($user->hasRole($role)) {
                $userRequires2FA = true;
                break;
            }
        }

        if ($userRequires2FA
            && $user->two_factor_enabled
            && $user->two_factor_confirmed_at === null
            && ! $request->routeIs('two-factor.*')
            && ! $request->routeIs('logout')
        ) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
