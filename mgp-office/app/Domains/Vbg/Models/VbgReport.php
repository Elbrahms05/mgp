<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Models;

use App\Domains\Site\Models\Site;
use App\Domains\Vbg\Enums\GbvimsClassification;
use App\Domains\Vbg\Enums\VbgAuthorLink;
use App\Domains\Vbg\Enums\VbgChannel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class VbgReport extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'vbg.vbg_reports';

    protected $fillable = [
        'code',
        'channel',
        'is_anonymous',
        'caller_phone_encrypted',
        'consent_given',
        'consent_given_at',
        'survivor_age_group',
        'survivor_gender',
        'incident_date',
        'incident_zone',
        'author_link',
        'classification',
        'services_received',
        'site_id',
        'trusted_person_id',
        'ussd_session_id',
        'raw_sms_content',
        'voice_memo_path',
        'received_by',
        'ugp_notified_at',
        'worldbank_notified_at',
        'status',
    ];

    protected $casts = [
        'channel'            => VbgChannel::class,
        'author_link'        => VbgAuthorLink::class,
        'classification'     => GbvimsClassification::class,
        'services_received'  => 'array',
        'is_anonymous'       => 'boolean',
        'consent_given'      => 'boolean',
        'consent_given_at'   => 'datetime',
        'incident_date'      => 'date',
        'ugp_notified_at'    => 'datetime',
        'worldbank_notified_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function trustedPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trusted_person_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(VbgReferral::class);
    }

    public function enquiry(): HasOne
    {
        return $this->hasOne(VbgEnquiry::class);
    }

    public function consent(): HasOne
    {
        return $this->hasOne(VbgConsent::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('voice_memos')->acceptsMimeTypes(['audio/ogg', 'audio/mpeg', 'audio/mp4']);
        $this->addMediaCollection('documents');
    }
}
