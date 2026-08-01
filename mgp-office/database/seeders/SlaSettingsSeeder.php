<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Settings\SlaSettings;
use Illuminate\Database\Seeder;

class SlaSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(SlaSettings::class);

        $settings->cbgp_exam_days = 3;
        $settings->cbgp_investigation_days = 7;
        $settings->ccgp_exam_days = 3;
        $settings->ccgp_investigation_days = 4;
        $settings->cngp_exam_days = 7;
        $settings->cngp_investigation_days = 3;
        $settings->vbg_ugp_notification_hours = 24;
        $settings->vbg_referral_hours = 72;
        $settings->vbg_worldbank_eas_hours = 24;
        $settings->vbg_worldbank_incident_hours = 48;
        $settings->vbg_enquiry_days = 30;
        $settings->worker_direct_level1_days = 7;
        $settings->worker_direct_level2_days = 14;
        $settings->worker_contractor_level1_working_days = 7;
        $settings->worker_contractor_level2_days = 7;
        $settings->alert_threshold_ratio = 0.8;

        $settings->save();
    }
}
