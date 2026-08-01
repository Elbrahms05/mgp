<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Vbg\Services\AnonymizationService;
use Illuminate\Console\Command;

class PurgeStaleVbgReports extends Command
{
    protected $signature   = 'vbg:purge-stale {--hours=72 : Seuil en heures avant purge}';
    protected $description = 'Purge les données identifiantes des signalements VBG directs sans prise en charge (conformité ESIRT 72h)';

    public function __construct(
        private readonly AnonymizationService $anonymization,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $count = $this->anonymization->purgeStaleDirectReports($hours);

        if ($count > 0) {
            $this->warn("⚠  {$count} signalement(s) VBG non pris en charge purgé(s) après {$hours}h.");
        } else {
            $this->info("Aucun signalement VBG en attente dépassant {$hours}h.");
        }

        $this->anonymization->rotateWebhookLogs();

        return self::SUCCESS;
    }
}
