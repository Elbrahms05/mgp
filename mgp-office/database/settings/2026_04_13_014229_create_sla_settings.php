<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sla.cbgp_exam_days', 3);
        $this->migrator->add('sla.cbgp_investigation_days', 7);
        $this->migrator->add('sla.ccgp_exam_days', 3);
        $this->migrator->add('sla.ccgp_investigation_days', 4);
        $this->migrator->add('sla.cngp_exam_days', 7);
        $this->migrator->add('sla.cngp_investigation_days', 3);
        $this->migrator->add('sla.vbg_ugp_notification_hours', 24);
        $this->migrator->add('sla.vbg_referral_hours', 72);
        $this->migrator->add('sla.vbg_worldbank_eas_hours', 24);
        $this->migrator->add('sla.vbg_worldbank_incident_hours', 48);
        $this->migrator->add('sla.vbg_enquiry_days', 30);
        $this->migrator->add('sla.worker_direct_level1_days', 7);
        $this->migrator->add('sla.worker_direct_level2_days', 14);
        $this->migrator->add('sla.worker_contractor_level1_working_days', 7);
        $this->migrator->add('sla.worker_contractor_level2_days', 7);
        $this->migrator->add('sla.alert_threshold_ratio', 0.8);
    }
};
