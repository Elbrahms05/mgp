<?php

declare(strict_types=1);

namespace App\Domains\Committee\Models;

use App\Domains\Committee\Enums\CommitteeLevel;
use App\Domains\Geography\Models\Locality;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Committee extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'level',
        'locality_id',
        'installation_date',
        'pv_installation_path',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'level'             => CommitteeLevel::class,
        'installation_date' => 'date',
        'is_active'         => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function locality(): BelongsTo
    {
        return $this->belongsTo(Locality::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }
}
