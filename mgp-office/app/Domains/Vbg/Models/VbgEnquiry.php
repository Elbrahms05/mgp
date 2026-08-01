<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VbgEnquiry extends Model
{
    protected $table = 'vbg.vbg_enquiries';

    protected $fillable = [
        'vbg_report_id',
        'opened_at',
        'deadline_at',
        'closed_at',
        'project_link_confirmed',
        'findings',
        'recommendations',
        'worldbank_notified_at',
        'opened_by',
    ];

    protected $casts = [
        'opened_at'               => 'datetime',
        'deadline_at'             => 'datetime',
        'closed_at'               => 'datetime',
        'worldbank_notified_at'   => 'datetime',
        'project_link_confirmed'  => 'boolean',
    ];

    public function vbgReport(): BelongsTo
    {
        return $this->belongsTo(VbgReport::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }
}
