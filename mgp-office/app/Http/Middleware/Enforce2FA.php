<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Settings\SecuritySettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ER-02 / ENF-02 - Impose le 2FA aux rôles sensibles (super_admin, coordonnateur_ugp,
 * specialiste_vbg). Tant que le 2FA n'est pas **confirmé**, l'utilisateur concerné est
 * bloqué sur la page de configuration : aucun accès au back-office n'est possible.
 */
class Enforce2FA
{
    public function __construct(private readonly SecuritySettings $securitySettings) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // En développement local, on n'impose pas le 2FA (confort de dev).
        // Il reste pleinement actif en staging/production.
        if (app()->environment('local')) {
            return $next($request);
        }

        // Pages autorisées même sans 2FA confirmé (sinon boucle de redirection).
        if (
            $request->routeIs('two-factor.*')
            || $request->routeIs('logout')
            || $request->routeIs('password.confirm')
            || $request->routeIs('locale.set')
        ) {
            return $next($request);
        }

        $requiresTwoFactor = collect($this->securitySettings->two_factor_required_roles)
            ->contains(fn (string $role): bool => $user->hasRole($role));

        if ($requiresTwoFactor && ! $user->hasConfirmedTwoFactor()) {
            return redirect()
                ->route('two-factor.setup')
                ->with('warning', "Votre rôle exige l'authentification à deux facteurs. "
                    . "Activez-la pour accéder au système.");
        }

        return $next($request);
    }
}
