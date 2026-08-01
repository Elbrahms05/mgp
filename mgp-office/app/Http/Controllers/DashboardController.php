<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Complaint\Models\Complaint;
use App\Domains\Geography\Models\Region;
use App\Domains\Statistic\Services\ChartDataService;
use App\Domains\Statistic\Services\KpiCalculatorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly KpiCalculatorService $kpiCalculator,
        private readonly ChartDataService $chartDataService,
    ) {}

    public function __invoke(Request $request): View
    {
        $kpis        = $this->kpiCalculator->getComplaintCounts();
        $slaBreaches = $this->kpiCalculator->getSlaBreaches();
        $resolutionRate = $this->kpiCalculator->getResolutionRate();
        $byType      = $this->chartDataService->getComplaintsByType();
        $byChannel   = $this->chartDataService->getComplaintsByChannel();
        $trend       = $this->chartDataService->getMonthlyTrend();

        // Plaintes récentes (dernières 8)
        $recentComplaints = Complaint::query()
            ->with(['complaintType', 'site'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // Statistiques VBG récentes (si l'utilisateur a accès)
        $vbgStats = null;
        if ($request->user()->can('vbg.view')) {
            $vbgStats = [
                'pending'   => Complaint::where('type', 'VBG')->where('status', 'pending_action')->count(),
                'referred'  => Complaint::where('type', 'VBG')->where('status', 'referred')->count(),
                'follow_up' => Complaint::where('type', 'VBG')->where('status', 'follow_up')->count(),
                'closed'    => Complaint::where('type', 'VBG')->where('status', 'resolved')->count(),
            ];
        }

        // Plaintes par région (aggregated stats via SQL)
        $byRegion = Region::query()
            ->select(DB::raw('
                regions.id,
                regions.name,
                COUNT(DISTINCT complaints.id) as total_complaints,
                SUM(CASE WHEN complaints.status IN (\'pending_review\', \'under_investigation\', \'referred\') THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN complaints.status = \'resolved\' THEN 1 ELSE 0 END) as resolved
            '))
            ->leftJoin('departments', 'regions.id', '=', 'departments.region_id')
            ->leftJoin('communes', 'departments.id', '=', 'communes.department_id')
            ->leftJoin('localities', 'communes.id', '=', 'localities.commune_id')
            ->leftJoin('sites', 'localities.id', '=', 'sites.locality_id')
            ->leftJoin('complaints', 'sites.id', '=', 'complaints.site_id')
            ->groupBy('regions.id', 'regions.name')
            ->orderByDesc('total_complaints')
            ->limit(10)
            ->get();

        return view('pages.dashboard', compact(
            'kpis', 'slaBreaches', 'resolutionRate', 'byType', 'byChannel', 'trend',
            'recentComplaints', 'vbgStats', 'byRegion'
        ));
    }
}
