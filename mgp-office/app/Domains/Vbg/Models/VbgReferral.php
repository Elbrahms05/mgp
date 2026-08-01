<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Models;

use App\Domains\Vbg\Enums\ReferralServiceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VbgReferral extends Model
{
    protected $table = 'vbg.vbg_referrals';

    protected $fillable = [
        'vbg_report_id',
        'service_type',
        'provider_name',
        'provider_contact',
        'referred_at',
        'deadline_at',
        'confirmed_at',
        'notes',
        'referred_by',
    ];

    protected $casts = [
        'service_type' => ReferralServiceType::class,
        'referred_at'  => 'datetime',
        'deadline_at'  => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function vbgReport(): BelongsTo
    {
        return $this->belongsTo(VbgReport::class);
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
}
