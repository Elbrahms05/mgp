<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Authentification de l'app mobile terrain (ENF-01) par jeton Sanctum.
 *
 * ⚠️ En production, cette API doit être exposée derrière TLS 1.3 avec authentification
 *    par certificat client des appareils terrain (mTLS - cf. claude-deploy.md / 4.3).
 *    Le jeton Sanctum est la seconde couche (identité de l'agent).
 */
class AuthController extends Controller
{
    /**
     * Émet un jeton d'accès pour un appareil terrain identifié.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:120'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants invalides.'],
            ]);
        }

        if ($user->is_active === false) {
            throw ValidationException::withMessages([
                'email' => ['Compte désactivé.'],
            ]);
        }

        // Abilities du jeton = permissions de l'agent (principe du moindre privilège).
        $abilities = $user->getAllPermissions()->pluck('name')->all() ?: ['*'];

        // Un jeton par appareil : on révoque l'ancien jeton du même appareil.
        $user->tokens()->where('name', $data['device_name'])->delete();

        $token = $user->createToken($data['device_name'], $abilities);

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'token' => $token->plainTextToken,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    /**
     * Profil de l'agent connecté (vérification de jeton).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    /**
     * Révoque le jeton courant (déconnexion de l'appareil).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }
}
