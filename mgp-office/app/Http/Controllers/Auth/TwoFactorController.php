<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

/**
 * Gestion du 2FA (TOTP) par l'utilisateur : activation, confirmation, désactivation,
 * codes de récupération. S'appuie sur les actions Fortify (ER-02 / ENF-02).
 */
class TwoFactorController extends Controller
{
    /**
     * Page de configuration : QR code + saisie du code de confirmation, ou état actif.
     */
    public function setup(Request $request): View
    {
        $user = $request->user();

        $qrSvg        = null;
        $secretKey    = null;
        $recoveryCodes = [];

        // Secret présent mais pas encore confirmé → afficher QR + saisie de confirmation.
        if ($user->two_factor_secret) {
            $qrSvg     = $user->twoFactorQrCodeSvg();
            $secretKey = decrypt($user->two_factor_secret);
        }

        if ($user->hasConfirmedTwoFactor()) {
            $recoveryCodes = $user->recoveryCodes();
        }

        return view('security.two-factor', [
            'enabled'       => (bool) $user->two_factor_secret,
            'confirmed'     => $user->hasConfirmedTwoFactor(),
            'qrSvg'         => $qrSvg,
            'secretKey'     => $secretKey,
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Génère le secret et les codes de récupération (état non confirmé).
     */
    public function enable(Request $request, EnableTwoFactorAuthentication $enable): RedirectResponse
    {
        $enable($request->user());

        return redirect()
            ->route('two-factor.setup')
            ->with('success', "Scannez le QR code puis saisissez le code à 6 chiffres pour confirmer.");
    }

    /**
     * Confirme le 2FA avec un code TOTP valide → 2FA actif.
     */
    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        try {
            $confirm($request->user(), $request->input('code'));
        } catch (ValidationException $e) {
            return back()->withErrors(['code' => "Code invalide. Réessayez avec le code affiché par votre application."]);
        }

        // Maintien du champ historique `two_factor_enabled` en cohérence.
        $request->user()->forceFill(['two_factor_enabled' => true])->save();

        return redirect()
            ->route('two-factor.setup')
            ->with('success', "Authentification à deux facteurs activée. Conservez vos codes de récupération.");
    }

    /**
     * Régénère les codes de récupération.
     */
    public function recoveryCodes(Request $request, GenerateNewRecoveryCodes $generate): RedirectResponse
    {
        $generate($request->user());

        return redirect()
            ->route('two-factor.setup')
            ->with('success', "Nouveaux codes de récupération générés.");
    }

    /**
     * Désactive le 2FA (interdit aux rôles pour lesquels il est obligatoire).
     */
    public function disable(Request $request, DisableTwoFactorAuthentication $disable): RedirectResponse
    {
        $required = collect(app(\App\Settings\SecuritySettings::class)->two_factor_required_roles)
            ->contains(fn (string $role): bool => $request->user()->hasRole($role));

        if ($required) {
            return back()->with('error', "Votre rôle impose le 2FA : il ne peut pas être désactivé.");
        }

        $disable($request->user());
        $request->user()->forceFill(['two_factor_enabled' => false])->save();

        return redirect()->route('two-factor.setup')->with('success', "Authentification à deux facteurs désactivée.");
    }
}
