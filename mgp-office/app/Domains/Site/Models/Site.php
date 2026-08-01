<?php

declare(strict_types=1);

namespace App\Domains\Site\Models;

use App\Domains\Committee\Models\Committee;
use App\Domains\Company\Models\Company;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Geography\Models\Locality;
use App\Domains\Site\Enums\SiteCategory;
use App\Domains\Site\Enums\SiteStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Site extends Model implements HasMedia
{
    use SoftDeletes, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'code',
        'name',
        'site_type_id',
        'category',
        'locality_id',
        'latitude',
        'longitude',
        'area_hectares',
        'beneficiaries_count',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'status',
        'description',
        'responsible_user_id',
        'committee_id',
        'risk_level',
    ];

    protected $casts = [
        'category'           => SiteCategory::class,
        'status'             => SiteStatus::class,
        'start_date'         => 'date',
        'expected_end_date'  => 'date',
        'actual_end_date'    => 'date',
        'latitude'           => 'decimal:8',
        'longitude'          => 'decimal:8',
        'area_hectares'      => 'decimal:4',
        'beneficiaries_count'=> 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function locality(): BelongsTo
    {
        return $this->belongsTo(Locality::class);
    }

    public function siteType(): BelongsTo
    {
        return $this->belongsTo(SiteType::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function contractors(): HasMany
    {
        return $this->hasMany(SiteContractor::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_site')
            ->withPivot('responsible_name', 'responsible_phone', 'responsible_email')
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents');
        $this->addMediaCollection('photos');
    }
}
