<?php

declare(strict_types=1);

namespace App\Http\Controllers\Statistic;

use App\Domains\Committee\Models\Committee;
use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Site\Models\Site;
use App\Domains\Statistic\Services\ChartDataService;
use App\Domains\Statistic\Services\KpiCalculatorService;
use App\Domains\Vbg\Models\VbgReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticController extends Controller
{
    public function __construct(
        private readonly KpiCalculatorService $kpiCalculator,
        private readonly ChartDataService $chartDataService,
    ) {}

    public function dashboard(Request $request): View
    {
        $kpis           = $this->kpiCalculator->getComplaintCounts();
        $slaBreaches    = $this->kpiCalculator->getSlaBreaches();
        $resolutionRate = $this->kpiCalculator->getResolutionRate();

        $byType    = $this->chartDataService->getComplaintsByType();
        $byChannel = $this->chartDataService->getComplaintsByChannel();
        $trend     = $this->chartDataService->getMonthlyTrend(12);
        $byRegion  = $this->getComplaintsByRegion();
        $avgDelays = $this->getAverageResolutionDelays();
        $committeeStats = $this->getCommitteeStats();

        $vbgStats = null;
        if ($request->user()->can('statistics.view_vbg_aggregated')) {
            $vbgStats = $this->getVbgAggregatedStats();
        }

        $criticalSites = Site::where('risk_level', 'critical')
            ->with('locality.commune.department.region')
            ->limit(5)
            ->get();

        return view('pages.statistics.dashboard', compact(
            'kpis', 'slaBreaches', 'resolutionRate',
            'byType', 'byChannel', 'trend', 'byRegion',
            'avgDelays', 'committeeStats', 'vbgStats', 'criticalSites'
        ));
    }

    private function getComplaintsByRegion(): array
    {
        try {
            $rows = Complaint::join('sites', 'complaints.site_id', '=', 'sites.id')
                ->join('localities', 'sites.locality_id', '=', 'localities.id')
                ->join('communes', 'localities.commune_id', '=', 'communes.id')
                ->join('departments', 'communes.department_id', '=', 'departments.id')
                ->join('regions', 'departments.region_id', '=', 'regions.id')
                ->selectRaw('regions.name as region_name, count(*) as total')
                ->groupBy('regions.name')
                ->orderByDesc('total')
                ->get();

            return [
                'labels' => $rows->pluck('region_name')->toArray(),
                'data'   => $rows->pluck('total')->toArray(),
            ];
        } catch (\Throwable) {
            return ['labels' => [], 'data' => []];
        }
    }

    private function getAverageResolutionDelays(): array
    {
        try {
            $resolved = Complaint::whereIn('status', [ComplaintStatus::RESOLVED->value, ComplaintStatus::CLOSED->value])
                ->whereNotNull('closed_at')
                ->selectRaw("
                    current_level,
                    round(avg(extract(epoch from (closed_at - created_at)) / 86400)::numeric, 1) as avg_days,
                    count(*) as cnt
                ")
                ->groupBy('current_level')
                ->get();

            return $resolved->keyBy('current_level')->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function getCommitteeStats(): array
    {
        try {
            return [
                'total'  => Committee::count(),
                'active' => Committee::where('is_active', true)->count(),
                'cbgp'   => Committee::where('level', 'cbgp')->count(),
                'ccgp'   => Committee::where('level', 'ccgp')->count(),
                'cngp'   => Committee::where('level', 'cngp')->count(),
            ];
        } catch (\Throwable) {
            return ['total' => 0, 'active' => 0, 'cbgp' => 0, 'ccgp' => 0, 'cngp' => 0];
        }
    }

    private function getVbgAggregatedStats(): array
    {
        try {
            $total     = VbgReport::count();
            $anonymous = VbgReport::where('is_anonymous', true)->count();
            $referred  = VbgReport::whereHas('referrals')->count();
            $byChannel = VbgReport::selectRaw('channel, count(*) as total')
                ->groupBy('channel')
                ->get()
                ->pluck('total', 'channel')
                ->toArray();

            return [
                'total'        => $total,
                'anonymous'    => $anonymous,
                'referred'     => $referred,
                'referral_rate'=> $total > 0 ? round(($referred / $total) * 100, 1) : 0,
                'by_channel'   => $byChannel,
            ];
        } catch (\Throwable) {
            return ['total' => 0, 'anonymous' => 0, 'referred' => 0, 'referral_rate' => 0, 'by_channel' => []];
        }
    }
}
