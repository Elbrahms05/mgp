<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

/**
 * Défi 2FA à la connexion (ER-02) : l'utilisateur a saisi son mot de passe, il doit
 * maintenant fournir un code TOTP (ou un code de récupération) pour finaliser la session.
 */
class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request, TwoFactorAuthenticationProvider $provider): RedirectResponse
    {
        $userId = $request->session()->get('login.id');

        if (! $userId) {
            return redirect()->route('login');
        }

        /** @var User|null $user */
        $user = User::find($userId);

        if (! $user || ! $user->hasConfirmedTwoFactor()) {
            $request->session()->forget(['login.id', 'login.remember']);

            return redirect()->route('login');
        }

        $code         = trim((string) $request->input('code'));
        $recoveryCode = trim((string) $request->input('recovery_code'));

        $authenticated = false;

        if ($recoveryCode !== '') {
            $valid = collect($user->recoveryCodes())->first(fn (string $c): bool => hash_equals($c, $recoveryCode));

            if ($valid !== null) {
                $user->replaceRecoveryCode($valid);
                $authenticated = true;
            }
        } elseif ($code !== '' && $provider->verify(decrypt($user->two_factor_secret), $code)) {
            $authenticated = true;
        }

        if (! $authenticated) {
            return back()->withErrors(['code' => "Code d'authentification invalide."]);
        }

        $remember = (bool) $request->session()->pull('login.remember', false);
        $request->session()->forget('login.id');

        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
