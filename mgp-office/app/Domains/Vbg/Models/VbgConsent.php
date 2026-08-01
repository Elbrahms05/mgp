<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VbgConsent extends Model
{
    protected $table = 'vbg.vbg_consents';

    protected $fillable = [
        'vbg_report_id',
        'consent_given',
        'consented_at',
        'withdrawn_at',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'consent_given' => 'boolean',
        'consented_at'  => 'datetime',
        'withdrawn_at'  => 'datetime',
    ];

    public function vbgReport(): BelongsTo
    {
        return $this->belongsTo(VbgReport::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
