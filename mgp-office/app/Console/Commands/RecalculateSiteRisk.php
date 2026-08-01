<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Site\Models\Site;
use App\Domains\Site\Services\SiteRiskAnalysisService;
use Illuminate\Console\Command;

class RecalculateSiteRisk extends Command
{
    protected $signature   = 'sites:recalculate-risk {--site= : Recalculer uniquement ce site (ID)}';
    protected $description = 'Recalcule le niveau de risque de tous les sites actifs (quotidien)';

    public function __construct(
        private readonly SiteRiskAnalysisService $riskService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $siteId = $this->option('site');

        $query = Site::whereNotIn('status', ['closed', 'cancelled']);

        if ($siteId) {
            $query->where('id', $siteId);
        }

        $sites   = $query->get();
        $updated = 0;

        foreach ($sites as $site) {
            try {
                $this->riskService->recalculate($site);
                $updated++;
            } catch (\Throwable $e) {
                $this->warn("Erreur site #{$site->id} : {$e->getMessage()}");
            }
        }

        $this->info("Niveau de risque recalculé pour {$updated} site(s).");

        return self::SUCCESS;
    }
}
