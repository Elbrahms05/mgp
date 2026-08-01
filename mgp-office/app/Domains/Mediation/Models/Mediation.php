<?php

declare(strict_types=1);

namespace App\Domains\Mediation\Models;

use App\Domains\Complaint\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Mediation extends Model implements HasMedia
{
    use LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'complaint_id',
        'session_date',
        'presided_by_name',
        'presided_by_role',
        'result',
        'is_agreement_reached',
        'pv_path',
        'facilitated_by',
    ];

    protected $casts = [
        'session_date'        => 'date',
        'is_agreement_reached'=> 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function facilitatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'facilitated_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('pv_documents')->singleFile();
    }
}
