<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Models;

use App\Domains\Committee\Models\Committee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeEvaluation extends Model
{
    protected $fillable = [
        'committee_id',
        'period_start',
        'period_end',
        'members_total',
        'members_operational',
        'complaints_registered',
        'complaints_processed',
        'complaints_resolved_local',
        'complaints_transmitted',
        'difficulties',
        'solutions',
        'community_feedback',
        'improvement_suggestions',
        'submitted_by',
        'submitted_at',
    ];

    protected $casts = [
        'period_start'               => 'date',
        'period_end'                 => 'date',
        'submitted_at'               => 'datetime',
        'members_total'              => 'integer',
        'members_operational'        => 'integer',
        'complaints_registered'      => 'integer',
        'complaints_processed'       => 'integer',
        'complaints_resolved_local'  => 'integer',
        'complaints_transmitted'     => 'integer',
    ];

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
