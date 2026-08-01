<?php

declare(strict_types=1);

namespace App\Domains\Worker\Models;

use App\Domains\Company\Models\Company;
use App\Domains\Worker\Enums\WorkerComplaintStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WorkerComplaint extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'registration_number',
        'worker_type',
        'company_id',
        'employer_name',
        'worker_name',
        'worker_phone',
        'subject',
        'description',
        'incident_date',
        'status',
        'level',
        'sla_deadline',
        'escalated_at',
        'resolved_at',
        'registered_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'sla_deadline'  => 'datetime',
        'escalated_at'  => 'datetime',
        'resolved_at'   => 'datetime',
        'status'        => WorkerComplaintStatus::class,
        'level'         => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(WorkerComplaintHistory::class)->orderBy('created_at');
    }

    // ── SLA helpers ────────────────────────────────────────────────────────

    public function isSlaBreached(): bool
    {
        return $this->sla_deadline !== null
            && $this->sla_deadline->isPast()
            && ! $this->status->isTerminal();
    }

    public function slaRatio(): float
    {
        if ($this->sla_deadline === null) return 0;
        $total   = $this->created_at->diffInMinutes($this->sla_deadline);
        $elapsed = $this->created_at->diffInMinutes(now());
        return $total > 0 ? min($elapsed / $total, 1) : 1;
    }

    /** Jours restants avant l'échéance SLA (négatif si dépassé). */
    public function slaDaysLeft(): int
    {
        if ($this->sla_deadline === null) return 0;
        return (int) now()->diffInDays($this->sla_deadline, false);
    }

    // ── Libellés circuit ───────────────────────────────────────────────────

    public function circuitLabel(): string
    {
        if ($this->worker_type === 'direct') {
            return $this->level === 1
                ? 'N1 - Resp. admin UGP → Coordonnateur (7j)'
                : 'N2 - CNGP (14j)';
        }

        return $this->level === 1
            ? 'N1 - Chef personnel → Directeur travaux (7j ouvrables)'
            : 'N2 - Spécialiste MGP UGP → RH (7j)';
    }
}
