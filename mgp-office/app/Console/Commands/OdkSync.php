<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\OdkBridge\Services\OdkSyncService;
use Illuminate\Console\Command;

class OdkSync extends Command
{
    protected $signature = 'odk:sync
                            {--vbg      : Synchroniser uniquement les formulaires VBG (F03)}
                            {--all      : Synchroniser tous les formulaires (y compris VBG)}
                            {--reset=   : Réinitialiser le curseur pour un form_id donné}';

    protected $description = 'Synchronise les soumissions ODK Central vers PACIPA-MGP';

    public function __construct(
        private readonly OdkSyncService $syncService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // Réinitialisation de curseur
        if ($formId = $this->option('reset')) {
            $this->syncService->resetCursor($formId);
            $this->warn("Curseur réinitialisé pour {$formId} - la prochaine sync reprendra depuis le début.");
            return self::SUCCESS;
        }

        // Sync VBG seul
        if ($this->option('vbg')) {
            $this->line('Synchronisation VBG (F03)…');
            $result = $this->syncService->syncVbg();
            $this->printResult($result);
            return self::SUCCESS;
        }

        // Sync tous formulaires (non-VBG)
        $this->line('Synchronisation des formulaires (F01/F02/F04/F05/F06/F07)…');
        $result = $this->syncService->sync();
        $this->printResult($result);

        // Si --all, synchroniser aussi VBG
        if ($this->option('all')) {
            $this->line('Synchronisation VBG (F03)…');
            $vbgResult = $this->syncService->syncVbg();
            $this->printResult($vbgResult);
        }

        return self::SUCCESS;
    }

    private function printResult(array $result): void
    {
        $this->info("  ✓ {$result['processed']} soumission(s) traitée(s)");
        if ($result['skipped'] > 0) {
            $this->line("  ~ {$result['skipped']} ignorée(s) (doublon ou non mappable)");
        }
        if ($result['errors'] > 0) {
            $this->warn("  ✗ {$result['errors']} erreur(s) - voir les logs");
        }
    }
}
