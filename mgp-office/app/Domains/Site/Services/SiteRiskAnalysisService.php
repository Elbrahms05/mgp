<?php

declare(strict_types=1);

namespace App\Domains\Site\Services;

use App\Domains\Site\Models\Site;
use App\Domains\Complaint\Enums\ComplaintStatus;

class SiteRiskAnalysisService
{
    private const THRESHOLDS = [
        'open_medium'   => 3,   // plaintes ouvertes → medium
        'open_high'     => 8,   // plaintes ouvertes → high
        'type_repeat'   => 3,   // même type en 30j → +1 niveau
    ];

    public function recalculate(Site $site): void
    {
        $site->loadCount([
            'complaints as open_count' => fn ($q) => $q->whereNotIn('status', [
                ComplaintStatus::RESOLVED->value,
                ComplaintStatus::CLOSED->value,
                ComplaintStatus::REJECTED->value,
            ]),
        ]);

        $openCount = $site->open_count;

        // Vérifier commission d'enquête VBG confirmée → critical
        $vbgConfirmed = \Illuminate\Support\Facades\DB::table('vbg.vbg_enquiries as e')
            ->join('vbg.vbg_reports as r', 'r.id', '=', 'e.vbg_report_id')
            ->where('r.site_id', $site->id)
            ->where('e.project_link_confirmed', true)
            ->exists();

        if ($vbgConfirmed) {
            $site->risk_level = 'critical';
            $site->saveQuietly();
            return;
        }

        // Vérifier récurrence : même type en 30 jours
        $hasRepeat = $site->complaints()
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotIn('status', [ComplaintStatus::REJECTED->value])
            ->selectRaw('complaint_type_id, count(*) as cnt')
            ->groupBy('complaint_type_id')
            ->having('cnt', '>=', self::THRESHOLDS['type_repeat'])
            ->exists();

        $level = 'low';

        if ($openCount >= self::THRESHOLDS['open_high']) {
            $level = 'high';
        } elseif ($openCount >= self::THRESHOLDS['open_medium']) {
            $level = 'medium';
        }

        // +1 niveau si récurrence
        if ($hasRepeat) {
            $level = match ($level) {
                'low'    => 'medium',
                'medium' => 'high',
                'high'   => 'critical',
                default  => 'critical',
            };
        }

        $site->risk_level = $level;
        $site->saveQuietly();
    }

    public function recalculateAll(): void
    {
        Site::withCount([
            'complaints as open_count' => fn ($q) => $q->whereNotIn('status', [
                ComplaintStatus::RESOLVED->value,
                ComplaintStatus::CLOSED->value,
                ComplaintStatus::REJECTED->value,
            ]),
        ])->each(function (Site $site): void {
            $this->recalculate($site);
        });
    }
}
