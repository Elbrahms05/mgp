<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Committee\Enums\CommitteeLevel;
use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Services\ComplaintWorkflowService;
use App\Settings\SlaSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CheckComplaintSla extends Command
{
    protected $signature   = 'complaints:check-sla
                              {--escalate : Auto-escalader les plaintes SLA dépassé (sans action utilisateur)}';
    protected $description = 'Vérifie les délais SLA des plaintes en cours et déclenche les alertes + auto-escalade';

    /** Utilisateur système pour les transitions d'auto-escalade */
    private const SYSTEM_USER_ID = 1;

    public function __construct(
        private readonly SlaSettings              $slaSettings,
        private readonly ComplaintWorkflowService $workflow,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $threshold = $this->slaSettings->alert_threshold_ratio; // ex: 0.8

        // Plaintes actives avec délai SLA défini
        $complaints = Complaint::whereNotNull('sla_deadline')
            ->whereNotIn('status', [
                ComplaintStatus::CLOSED->value,
                ComplaintStatus::REJECTED->value,
                ComplaintStatus::RESOLVED->value,
            ])
            ->get();

        $breached = 0;
        $warned   = 0;

        foreach ($complaints as $complaint) {
            $total    = $complaint->created_at->diffInMinutes($complaint->sla_deadline);
            $elapsed  = $complaint->created_at->diffInMinutes(now());
            $ratio    = $total > 0 ? $elapsed / $total : 1;

            if ($complaint->sla_deadline->isPast()) {
                // SLA dépassé
                $breached++;
                Log::channel('sla')->warning('SLA breached', [
                    'registration_number' => $complaint->registration_number,
                    'deadline'            => $complaint->sla_deadline->toDateTimeString(),
                    'overdue_minutes'     => now()->diffInMinutes($complaint->sla_deadline),
                ]);
            } elseif ($ratio >= $threshold) {
                // Seuil d'alerte atteint (ex: 80% du délai écoulé)
                $warned++;
                Log::channel('sla')->info('SLA alert threshold reached', [
                    'registration_number' => $complaint->registration_number,
                    'ratio'               => round($ratio * 100) . '%',
                    'deadline'            => $complaint->sla_deadline->toDateTimeString(),
                ]);
            }
        }

        // Auto-escalade si demandé (option --escalate ou par défaut en production)
        $autoEscalate = $this->option('escalate') || config('app.env') === 'production';
        $escalated    = 0;

        if ($autoEscalate && $breached > 0) {
            $escalated = $this->autoEscalateBreached();
        }

        if ($breached > 0) {
            $this->warn("{$breached} plainte(s) ont dépassé leur délai SLA.");
        }
        if ($escalated > 0) {
            $this->warn("{$escalated} plainte(s) auto-escaladée(s) vers le niveau supérieur.");
        }
        if ($warned > 0) {
            $this->line("{$warned} plainte(s) approchent de leur délai SLA ({$threshold}% atteint).");
        }
        if ($breached === 0 && $warned === 0) {
            $this->info('Tous les délais SLA sont respectés.');
        }

        return self::SUCCESS;
    }

    /**
     * Auto-escalade des plaintes dont le SLA est dépassé et qui n'ont pas encore
     * été transmises au niveau supérieur.
     *
     * Règle spec (claude-metier.md) :
     *   CBGP → CCGP si 13j dépassés sans résolution
     *   CCGP → CNGP si 10j dépassés sans résolution
     *   CNGP dépassé → notification Coordonnateur (pas de niveau supérieur)
     */
    private function autoEscalateBreached(): int
    {
        $systemUser = \App\Models\User::find(self::SYSTEM_USER_ID);
        if (! $systemUser) return 0;

        $escalated = 0;

        $breachedComplaints = Complaint::whereNotIn('status', [
            ComplaintStatus::CLOSED->value,
            ComplaintStatus::REJECTED->value,
            ComplaintStatus::RESOLVED->value,
            ComplaintStatus::ESCALATED->value,
        ])
            ->whereNotNull('sla_deadline')
            ->where('sla_deadline', '<', now())
            ->get();

        foreach ($breachedComplaints as $complaint) {
            try {
                $nextLevel = match ($complaint->current_level) {
                    CommitteeLevel::CBGP => CommitteeLevel::CCGP,
                    CommitteeLevel::CCGP => CommitteeLevel::CNGP,
                    default              => null,
                };

                if ($nextLevel === null) {
                    // CNGP dépassé - notification Coordonnateur uniquement
                    Log::channel('sla')->critical('SLA CNGP dépassé sans escalade possible', [
                        'registration_number' => $complaint->registration_number,
                    ]);
                    continue;
                }

                $this->workflow->escalate(
                    $complaint,
                    $nextLevel,
                    "Auto-escalade système : délai SLA dépassé au niveau précédent ({$complaint->current_level->label()})",
                    $systemUser,
                );

                $escalated++;

                Log::channel('sla')->warning('Auto-escalade SLA', [
                    'registration_number' => $complaint->registration_number,
                    'from'                => $complaint->getOriginal('current_level'),
                    'to'                  => $nextLevel->value,
                ]);
            } catch (\Throwable $e) {
                Log::error("Auto-escalade impossible pour {$complaint->registration_number} : {$e->getMessage()}");
            }
        }

        return $escalated;
    }
}
