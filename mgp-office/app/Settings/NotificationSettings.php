<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    /** Canaux actifs par événement. Format : ['event_key' => ['sms', 'email', 'database']] */
    public array $channels = [
        'complaint_registered'   => ['sms', 'database'],
        'complaint_escalated'    => ['sms', 'email', 'database'],
        'complaint_resolved'     => ['sms', 'database'],
        'sla_breach'             => ['email', 'database'],
        'sla_reminder'           => ['database'],
        'vbg_received_by_ugp'    => ['email', 'database'],
        'vbg_referral_confirmed' => ['database'],
        'worldbank_esirt'        => ['email'],
        'worldbank_pees'         => ['email'],
    ];

    /** Destinataires par événement. Format : ['event_key' => ['role' => 'role_name', 'email' => 'addr@...']] */
    public array $recipients = [
        'sla_breach'          => ['roles' => ['coordonnateur_ugp', 'chef_urc']],
        'vbg_received_by_ugp' => ['roles' => ['specialiste_vbg', 'coordonnateur_ugp']],
        'worldbank_esirt'     => ['emails' => []],
        'worldbank_pees'      => ['emails' => []],
    ];

    /**
     * Destinataires de l'envoi automatique périodique des rapports (EF-05/EF-14/ER-04).
     * Format : ['report_type' => ['enabled' => bool, 'roles' => [...], 'emails' => [...]]]
     */
    public array $report_recipients = [
        'monthly_dashboard' => ['enabled' => true, 'roles' => ['coordonnateur_ugp'], 'emails' => []],
        'quarterly_vbg'     => ['enabled' => true, 'roles' => ['specialiste_vbg', 'coordonnateur_ugp'], 'emails' => []],
        'annual_mgp'        => ['enabled' => true, 'roles' => ['coordonnateur_ugp'], 'emails' => []],
    ];

    /** Fréquence des rappels SLA (minutes) */
    public int $sla_reminder_frequency_minutes = 60;

    /** Activer les notifications email */
    public bool $email_enabled = true;

    /** Activer les notifications SMS */
    public bool $sms_enabled = false;

    public static function group(): string
    {
        return 'notifications';
    }
}
