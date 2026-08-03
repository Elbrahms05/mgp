<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * ENF-02 - La sauvegarde chiffrée est configurée (rétention 90 j, chiffrement de
 * l'archive) et réellement planifiée dans routes/console.php.
 */
class SpatieBackupTest extends TestCase
{
    public function test_les_commandes_backup_sont_disponibles(): void
    {
        $commands = array_keys(Artisan::all());

        $this->assertContains('backup:run', $commands);
        $this->assertContains('backup:clean', $commands);
        $this->assertContains('backup:monitor', $commands);
    }

    public function test_retention_90_jours_et_chiffrement_actifs(): void
    {
        $this->assertSame(90, config('backup.cleanup.default_strategy.keep_daily_backups_for_days'));
        $this->assertSame('default', config('backup.backup.encryption'));
    }

    public function test_la_sauvegarde_est_planifiee_quotidiennement(): void
    {
        Artisan::call('schedule:list');
        $output = Artisan::output();

        $this->assertStringContainsString('backup:run', $output);
        $this->assertStringContainsString('backup:clean', $output);
    }
}
