<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SecuritySettings extends Settings
{
    /** Durée de session en minutes */
    public int $session_lifetime_minutes = 120;

    /** 2FA obligatoire pour ces rôles */
    public array $two_factor_required_roles = [
        'super_admin',
        'coordonnateur_ugp',
        'specialiste_vbg',
    ];

    /** Politique mots de passe */
    public int $password_min_length = 10;
    public bool $password_require_uppercase = true;
    public bool $password_require_number = true;
    public bool $password_require_symbol = true;
    public int $password_expiry_days = 90;

    /** Rotation des clés de chiffrement VBG (jours) */
    public int $vbg_key_rotation_days = 365;

    /** IP whitelist pour les rôles sensibles (vide = désactivé) */
    public array $ip_whitelist = [];

    /** Nombre max de tentatives de connexion avant blocage */
    public int $max_login_attempts = 5;

    /** Durée du blocage après échecs de connexion (minutes) */
    public int $lockout_duration_minutes = 15;

    public static function group(): string
    {
        return 'security';
    }
}
