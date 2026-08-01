<?php

declare(strict_types=1);

namespace App\Domains\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CommunicationCampaign extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'type',
        'description',
        'regions',
        'target_audience',
        'languages',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'regions'        => 'array',
        'languages'      => 'array',
        'start_date'     => 'date',
        'end_date'       => 'date',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('materials');
    }
}
