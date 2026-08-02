<?php

declare(strict_types=1);

namespace App\Domains\Complaint\Models;

use App\Domains\Committee\Enums\CommitteeLevel;
use App\Domains\Complaint\Enums\ComplaintChannel;
use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Site\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;

class Complaint extends Model implements HasMedia
{
    use SoftDeletes, LogsActivity, InteractsWithMedia, HasTags;

    protected $fillable = [
        'registration_number',
        'complaint_type_id',
        'site_id',
        'complainant_name',
        'complainant_age',
        'complainant_gender',
        'complainant_locality',
        'complainant_phone',
        'is_anonymous',
        'subject',
        'description',
        'incident_date',
        'accused_party',
        'channel',
        'received_at',
        'status',
        'current_level',
        'assigned_to',
        'registered_by',
        'sla_deadline',
        'closed_at',
    ];

    protected $casts = [
        'channel'       => ComplaintChannel::class,
        'status'        => ComplaintStatus::class,
        'current_level' => CommitteeLevel::class,
        'incident_date' => 'date',
        'received_at'   => 'datetime',
        'sla_deadline'  => 'datetime',
        'closed_at'     => 'datetime',
        'is_anonymous'  => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at']);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function complaintType(): BelongsTo
    {
        return $this->belongsTo(ComplaintType::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ComplaintHistory::class);
    }

    public function mediations(): HasMany
    {
        return $this->hasMany(\App\Domains\Mediation\Models\Mediation::class);
    }

    public function satisfactionSurvey(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Domains\Evaluation\Models\SatisfactionSurvey::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');

        // Accusé de réception PDF (EF-06) : un seul fichier, disque privé.
        $this->addMediaCollection('acknowledgment')
            ->useDisk('local')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf']);
    }
}
