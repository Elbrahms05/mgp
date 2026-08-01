<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Worker\Enums\WorkerComplaintStatus;
use App\Domains\Worker\Models\WorkerComplaint;
use App\Domains\Worker\Services\WorkerComplaintWorkflowService;
use App\Settings\SlaSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Vérifie les SLA des plaintes travailleurs et auto-escalade si dépassé au N1.
 *
 * Scheduler : toutes les heures (voir app/Console/Kernel.php ou routes/console.php).
 *   $schedule->command('workers:check-sla --escalate')->hourly();
 */
class CheckWorkerSla extends Command
{
    protected $signature   = 'workers:check-sla
                              {--escalate : Auto-escalader N1→N2 si SLA dépassé}';
    protected $description = 'Vérifie les délais SLA des plaintes travailleurs et déclenche alertes + auto-escalade N1→N2';

    private const SYSTEM_USER_ID = 1;

    public function __construct(
        private readonly SlaSettings                    $slaSettings,
        private readonly WorkerComplaintWorkflowService $workflow,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $active = WorkerComplaint::whereNotNull('sla_deadline')
            ->whereNotIn('status', [
                WorkerComplaintStatus::RESOLVED->value,
                WorkerComplaintStatus::CLOSED->value,
            ])
            ->get();

        $breached  = 0;
        $warned    = 0;
        $escalated = 0;

        foreach ($active as $complaint) {
            if ($complaint->isSlaBreached()) {
                $breached++;
                Log::channel('sla')->warning('Worker SLA breached', [
                    'ref'      => $complaint->registration_number,
                    'level'    => $complaint->level,
                    'type'     => $complaint->worker_type,
                    'deadline' => $complaint->sla_deadline->toDateTimeString(),
                ]);
            } elseif ($complaint->slaRatio() >= $this->slaSettings->alert_threshold_ratio) {
                $warned++;
                Log::channel('sla')->info('Worker SLA alert threshold', [
                    'ref'   => $complaint->registration_number,
                    'ratio' => round($complaint->slaRatio() * 100) . '%',
                ]);
            }
        }

        // Auto-escalade N1 → N2 uniquement (pas de niveau supérieur au N2)
        if (($this->option('escalate') || app()->isProduction()) && $breached > 0) {
            $system = \App\Models\User::find(self::SYSTEM_USER_ID);

            if ($system) {
                $toEscalate = WorkerComplaint::whereNotNull('sla_deadline')
                    ->where('sla_deadline', '<', now())
                    ->where('level', 1)
                    ->where('status', WorkerComplaintStatus::UNDER_REVIEW->value)
                    ->get();

                foreach ($toEscalate as $complaint) {
                    try {
                        $this->workflow->autoEscalate($complaint, $system);
                        $escalated++;
                        Log::channel('sla')->warning('Worker auto-escalade N1→N2', [
                            'ref'  => $complaint->registration_number,
                            'type' => $complaint->worker_type,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error("Auto-escalade worker impossible pour {$complaint->registration_number}: {$e->getMessage()}");
                    }
                }

                // N2 dépassé → log critique, pas d'escalade possible
                $n2Breached = WorkerComplaint::whereNotNull('sla_deadline')
                    ->where('sla_deadline', '<', now())
                    ->where('level', 2)
                    ->whereNotIn('status', [WorkerComplaintStatus::RESOLVED->value, WorkerComplaintStatus::CLOSED->value])
                    ->get();

                foreach ($n2Breached as $complaint) {
                    Log::channel('sla')->critical('Worker SLA N2 dépassé - action coordinateur requise', [
                        'ref'  => $complaint->registration_number,
                        'type' => $complaint->worker_type,
                    ]);
                }
            }
        }

        $breached  > 0 && $this->warn("{$breached} plainte(s) travailleur ont dépassé le SLA.");
        $escalated > 0 && $this->warn("{$escalated} plainte(s) auto-escaladée(s) N1→N2.");
        $warned    > 0 && $this->line("{$warned} plainte(s) approchent du délai SLA.");
        ($breached + $warned) === 0 && $this->info('Tous les SLA travailleurs sont respectés.');

        return self::SUCCESS;
    }
}
