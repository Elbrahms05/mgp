<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SlaSettings extends Settings
{
    // CBGP - Niveau 1 (jours)
    public int $cbgp_exam_days = 3;
    public int $cbgp_investigation_days = 7;

    // CCGP - Niveau 2 (jours)
    public int $ccgp_exam_days = 3;
    public int $ccgp_investigation_days = 4;

    // CNGP - Niveau 3 (jours)
    public int $cngp_exam_days = 7;
    public int $cngp_investigation_days = 3;

    // VBG - délais en heures
    public int $vbg_ugp_notification_hours = 24;
    public int $vbg_referral_hours = 72;
    public int $vbg_worldbank_eas_hours = 24;
    public int $vbg_worldbank_incident_hours = 48;
    public int $vbg_enquiry_days = 30;

    // Travailleurs directs (UGP)
    public int $worker_direct_level1_days = 7;
    public int $worker_direct_level2_days = 14;

    // Travailleurs contractuels
    public int $worker_contractor_level1_working_days = 7;
    public int $worker_contractor_level2_days = 7;

    /** Délai avant alerte (fraction du délai total, ex: 0.8 = alerte à 80% du délai) */
    public float $alert_threshold_ratio = 0.8;

    public static function group(): string
    {
        return 'sla';
    }
}
