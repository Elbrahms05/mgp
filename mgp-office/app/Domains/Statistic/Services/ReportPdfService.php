<?php

declare(strict_types=1);

namespace App\Domains\Statistic\Services;

use App\Settings\ProjectSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Carbon\Carbon;

/**
 * Génère les états PDF du MGP (EF-05 / EF-14 / ER-04) : tableau de bord mensuel,
 * rapport VBG agrégé trimestriel (anonymisé), rapport annuel MGP.
 *
 * Le rapport VBG n'utilise QUE des indicateurs agrégés (KpiCalculatorService::getVbgKpis) :
 * aucune donnée nominative ne circule (règle critique n°1).
 */
class ReportPdfService
{
    public function __construct(
        private readonly KpiCalculatorService $kpi,
        private readonly ChartDataService $charts,
        private readonly ProjectSettings $project,
    ) {}

    public function monthlyDashboard(Carbon $from, Carbon $to): DomPDF
    {
        return Pdf::loadView('pdf.reports.monthly-dashboard', [
            'project'      => $this->project,
            'from'         => $from,
            'to'           => $to,
            'kpis'         => $this->kpi->getComplaintCounts(),
            'resolution'   => $this->kpi->getResolutionRate(),
            'satisfaction' => $this->kpi->getSatisfactionStats(),
            'byType'       => $this->charts->getComplaintsByType(),
            'byChannel'    => $this->charts->getComplaintsByChannel(),
            'avgDays'      => $this->kpi->getAvgResolutionDaysByLevel(),
            'generatedAt'  => now(),
        ])->setPaper('a4');
    }

    public function quarterlyVbgAggregate(Carbon $from, Carbon $to): DomPDF
    {
        return Pdf::loadView('pdf.reports.vbg-aggregate', [
            'project'     => $this->project,
            'from'        => $from,
            'to'          => $to,
            'vbg'         => $this->kpi->getVbgKpis(),
            'generatedAt' => now(),
        ])->setPaper('a4');
    }

    public function annualMgp(Carbon $from, Carbon $to): DomPDF
    {
        return Pdf::loadView('pdf.reports.monthly-dashboard', [
            'project'      => $this->project,
            'from'         => $from,
            'to'           => $to,
            'kpis'         => $this->kpi->getComplaintCounts(),
            'resolution'   => $this->kpi->getResolutionRate(),
            'satisfaction' => $this->kpi->getSatisfactionStats(),
            'byType'       => $this->charts->getComplaintsByType(),
            'byChannel'    => $this->charts->getComplaintsByChannel(),
            'avgDays'      => $this->kpi->getAvgResolutionDaysByLevel(),
            'generatedAt'  => now(),
            'annual'       => true,
        ])->setPaper('a4');
    }

    /**
     * Rend l'état PDF correspondant au type de rapport, ou null si non géré.
     */
    public function forType(string $type, Carbon $from, Carbon $to): ?DomPDF
    {
        return match ($type) {
            'monthly_dashboard' => $this->monthlyDashboard($from, $to),
            'quarterly_vbg'     => $this->quarterlyVbgAggregate($from, $to),
            'annual_mgp'        => $this->annualMgp($from, $to),
            default             => null,
        };
    }
}
