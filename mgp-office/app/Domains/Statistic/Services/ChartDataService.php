<?php

declare(strict_types=1);

namespace App\Domains\Statistic\Services;

use App\Domains\Complaint\Models\Complaint;
use App\Domains\Site\Models\Site;
use Illuminate\Support\Facades\DB;

class ChartDataService
{
    public function getComplaintsByType(): array
    {
        try {
            $rows = Complaint::join('complaint_types', 'complaints.complaint_type_id', '=', 'complaint_types.id')
                ->select('complaint_types.label', DB::raw('count(*) as total'))
                ->groupBy('complaint_types.label')
                ->orderByDesc('total')
                ->get();

            return [
                'labels' => $rows->pluck('label')->toArray(),
                'data'   => $rows->pluck('total')->toArray(),
            ];
        } catch (\Throwable) {
            return ['labels' => [], 'data' => []];
        }
    }

    public function getComplaintsByChannel(): array
    {
        try {
            $rows = Complaint::selectRaw('channel, count(*) as total')
                ->groupBy('channel')
                ->get();

            return [
                'labels' => $rows->pluck('channel')->toArray(),
                'data'   => $rows->pluck('total')->toArray(),
            ];
        } catch (\Throwable) {
            return ['labels' => [], 'data' => []];
        }
    }

    public function getMonthlyTrend(int $months = 12): array
    {
        try {
            $rows = Complaint::selectRaw("to_char(created_at, 'YYYY-MM') as month, count(*) as total")
                ->where('created_at', '>=', now()->subMonths($months))
                ->groupByRaw("to_char(created_at, 'YYYY-MM')")
                ->orderBy('month')
                ->get();

            return [
                'labels' => $rows->pluck('month')->toArray(),
                'data'   => $rows->pluck('total')->toArray(),
            ];
        } catch (\Throwable) {
            return ['labels' => [], 'data' => []];
        }
    }

    /**
     * Heatmap des plaintes par région Niger (pour la carte interactive du dashboard).
     *
     * @return array{
     *     regions: array<string, array{
     *         name: string,
     *         total: int,
     *         open: int,
     *         sla_breached: int,
     *         risk_level: string
     *     }>
     * }
     */
    public function getComplaintsByRegion(): array
    {
        // 8 régions Niger (codes courts utilisés dans les sites et formulaires)
        $regionNames = [
            'AGZ' => 'Agadez',
            'DIF' => 'Diffa',
            'DOS' => 'Dosso',
            'MAR' => 'Maradi',
            'NIA' => 'Niamey',
            'TAH' => 'Tahoua',
            'TIL' => 'Tillabéri',
            'ZIN' => 'Zinder',
        ];

        $result = [];

        try {
            // Comptage via la jointure sites → registration_number préfixé par région
            // ex: "AGZ-NIY-2025-0001" → premier segment = code région
            $rows = DB::table('complaints')
                ->selectRaw("
                    SPLIT_PART(registration_number, '-', 1) AS region_code,
                    COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE status NOT IN ('closed','rejected','resolved')) AS open,
                    COUNT(*) FILTER (WHERE sla_deadline < NOW()
                        AND status NOT IN ('closed','rejected','resolved')) AS sla_breached
                ")
                ->groupByRaw("SPLIT_PART(registration_number, '-', 1)")
                ->get()
                ->keyBy('region_code');

            // Niveau de risque maximal des sites par région
            $siteRiskByRegion = Site::join('localities', 'sites.locality_id', '=', 'localities.id')
                ->join('communes', 'localities.commune_id', '=', 'communes.id')
                ->join('departments', 'communes.department_id', '=', 'departments.id')
                ->join('regions', 'departments.region_id', '=', 'regions.id')
                ->selectRaw("regions.code AS region_code, MAX(
                    CASE sites.risk_level
                        WHEN 'critical' THEN 4
                        WHEN 'high'     THEN 3
                        WHEN 'medium'   THEN 2
                        ELSE 1
                    END
                ) AS max_risk")
                ->groupBy('regions.code')
                ->pluck('max_risk', 'region_code')
                ->map(fn ($v) => match ((int) $v) {
                    4 => 'critical', 3 => 'high', 2 => 'medium', default => 'low',
                })
                ->toArray();

            foreach ($regionNames as $code => $name) {
                $row = $rows[$code] ?? null;
                $result[$code] = [
                    'name'        => $name,
                    'total'       => (int) ($row?->total ?? 0),
                    'open'        => (int) ($row?->open ?? 0),
                    'sla_breached'=> (int) ($row?->sla_breached ?? 0),
                    'risk_level'  => $siteRiskByRegion[$code] ?? 'low',
                ];
            }
        } catch (\Throwable) {
            foreach ($regionNames as $code => $name) {
                $result[$code] = [
                    'name' => $name, 'total' => 0, 'open' => 0,
                    'sla_breached' => 0, 'risk_level' => 'low',
                ];
            }
        }

        return ['regions' => $result];
    }

    /**
     * Répartition par niveau de comité (CBGP / CCGP / CNGP).
     */
    public function getComplaintsByLevel(): array
    {
        try {
            $rows = Complaint::selectRaw('current_level, count(*) as total')
                ->groupBy('current_level')
                ->get();

            return [
                'labels' => $rows->pluck('current_level')->toArray(),
                'data'   => $rows->pluck('total')->toArray(),
            ];
        } catch (\Throwable) {
            return ['labels' => [], 'data' => []];
        }
    }
}
