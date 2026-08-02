<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Destinataires de l'envoi automatique périodique des rapports (EF-05 / EF-14 / ER-04).
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('notifications.report_recipients', [
            'monthly_dashboard' => ['enabled' => true, 'roles' => ['coordonnateur_ugp'], 'emails' => []],
            'quarterly_vbg'     => ['enabled' => true, 'roles' => ['specialiste_vbg', 'coordonnateur_ugp'], 'emails' => []],
            'annual_mgp'        => ['enabled' => true, 'roles' => ['coordonnateur_ugp'], 'emails' => []],
        ]);
    }
};
