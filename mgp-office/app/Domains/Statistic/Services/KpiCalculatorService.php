<?php

declare(strict_types=1);

namespace App\Domains\Statistic\Services;

use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Evaluation\Models\SatisfactionSurvey;
use App\Domains\Vbg\Enums\VbgChannel;
use App\Domains\Vbg\Models\VbgReport;
use Illuminate\Support\Collection;

class KpiCalculatorService
{
    public function getComplaintCounts(): array
    {
        try {
            $counts = Complaint::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();
        } catch (\Throwable) {
            return $this->emptyKpis();
        }

        return [
            'total'      => array_sum($counts),
            'registered' => $counts[ComplaintStatus::REGISTERED->value] ?? 0,
            'in_progress'=> ($counts[ComplaintStatus::UNDER_REVIEW->value] ?? 0)
                          + ($counts[ComplaintStatus::ACCEPTED->value] ?? 0)
                          + ($counts[ComplaintStatus::IN_MEDIATION->value] ?? 0),
            'resolved'   => $counts[ComplaintStatus::RESOLVED->value] ?? 0,
            'escalated'  => $counts[ComplaintStatus::ESCALATED->value] ?? 0,
            'closed'     => $counts[ComplaintStatus::CLOSED->value] ?? 0,
            'rejected'   => $counts[ComplaintStatus::REJECTED->value] ?? 0,
        ];
    }

    public function getSlaBreaches(): Collection
    {
        try {
            return Complaint::whereNotIn('status', [
                    ComplaintStatus::CLOSED->value,
                    ComplaintStatus::REJECTED->value,
                ])
                ->where('sla_deadline', '<', now())
                ->with(['site', 'complaintType'])
                ->orderBy('sla_deadline')
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    public function getResolutionRate(): float
    {
        try {
            $total = Complaint::count();
            if ($total === 0) return 0.0;

            $resolved = Complaint::whereIn('status', [
                ComplaintStatus::RESOLVED->value,
                ComplaintStatus::CLOSED->value,
            ])->count();

            return round(($resolved / $total) * 100, 1);
        } catch (\Throwable) {
            return 0.0;
        }
    }

    /**
     * Métriques VBG agrégées et anonymisées (Tableau 7 - claude-metier.md).
     * ⚠️  Accès restreint : specialiste_vbg + coordonnateur_ugp uniquement
     *     (contrôle au niveau contrôleur via @can('vbg.view_decrypted')).
     *
     * @return array{
     *     total: int,
     *     direct_by_survivor_pct: float,
     *     via_third_party_pct: float,
     *     referral_rate_pct: float,
     *     by_channel: array<string, int>,
     *     services_referral: array<string, int>
     * }
     */
    public function getVbgKpis(): array
    {
        try {
            $total = VbgReport::count();
            if ($total === 0) {
                return $this->emptyVbgKpis();
            }

            // Canaux directs de la survivante (USSD, SMS, WhatsApp, Ligne Verte)
            $directChannels = [
                VbgChannel::USSD->value,
                VbgChannel::SMS->value,
                VbgChannel::WHATSAPP->value,
                VbgChannel::VOICE_LINE->value,
            ];

            $directCount = VbgReport::whereIn('channel', $directChannels)->count();

            // Via tiers (Personne de confiance, Point focal)
            $thirdPartyChannels = [
                VbgChannel::TRUSTED_PERSON->value,
                VbgChannel::POINT_FOCAL->value,
            ];
            $thirdPartyCount = VbgReport::whereIn('channel', $thirdPartyChannels)->count();

            // Taux de référencement : rapports avec consent_given = true
            $referred = VbgReport::where('consent_given', true)->count();

            // Répartition par canal
            $byChannel = VbgReport::selectRaw('channel, count(*) as total')
                ->groupBy('channel')
                ->pluck('total', 'channel')
                ->toArray();

            // Répartition des services reçus (JSON array dans services_received)
            $servicesRaw = VbgReport::whereNotNull('services_received')
                ->pluck('services_received')
                ->flatten()
                ->countBy()
                ->toArray();

            return [
                'total'                  => $total,
                'direct_by_survivor_pct' => round(($directCount / $total) * 100, 1),
                'via_third_party_pct'    => round(($thirdPartyCount / $total) * 100, 1),
                'referral_rate_pct'      => round(($referred / $total) * 100, 1),
                'by_channel'             => $byChannel,
                'services_referral'      => $servicesRaw,
            ];
        } catch (\Throwable) {
            return $this->emptyVbgKpis();
        }
    }

    /**
     * Délai moyen de résolution par niveau de comité (en jours).
     *
     * @return array<string, float>  ex: ['cbgp' => 8.5, 'ccgp' => 6.2, 'cngp' => 5.0]
     */
    public function getAvgResolutionDaysByLevel(): array
    {
        try {
            $rows = Complaint::whereNotNull('closed_at')
                ->whereIn('status', [ComplaintStatus::RESOLVED->value, ComplaintStatus::CLOSED->value])
                ->selectRaw("current_level, AVG(EXTRACT(EPOCH FROM (closed_at - created_at)) / 86400) as avg_days")
                ->groupBy('current_level')
                ->pluck('avg_days', 'current_level')
                ->map(fn ($v) => round((float) $v, 1))
                ->toArray();

            return $rows;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Indicateurs de satisfaction du plaignant (EF-08).
     *
     * @return array{count:int, avg_overall:float, satisfied_pct:float, recommend_pct:float}
     */
    public function getSatisfactionStats(): array
    {
        try {
            $surveys = SatisfactionSurvey::query();
            $count   = (clone $surveys)->count();

            if ($count === 0) {
                return ['count' => 0, 'avg_overall' => 0.0, 'satisfied_pct' => 0.0, 'recommend_pct' => 0.0];
            }

            $avg       = (float) (clone $surveys)->avg('overall_rating');
            $satisfied = (clone $surveys)->where('overall_rating', '>=', 4)->count();
            $recommend = (clone $surveys)->where('would_recommend', true)->count();

            return [
                'count'         => $count,
                'avg_overall'   => round($avg, 2),
                'satisfied_pct' => round($satisfied / $count * 100, 1),
                'recommend_pct' => round($recommend / $count * 100, 1),
            ];
        } catch (\Throwable) {
            return ['count' => 0, 'avg_overall' => 0.0, 'satisfied_pct' => 0.0, 'recommend_pct' => 0.0];
        }
    }

    private function emptyKpis(): array
    {
        return [
            'total' => 0, 'registered' => 0, 'in_progress' => 0,
            'resolved' => 0, 'escalated' => 0, 'closed' => 0, 'rejected' => 0,
        ];
    }

    private function emptyVbgKpis(): array
    {
        return [
            'total'                  => 0,
            'direct_by_survivor_pct' => 0.0,
            'via_third_party_pct'    => 0.0,
            'referral_rate_pct'      => 0.0,
            'by_channel'             => [],
            'services_referral'      => [],
        ];
    }
}
