<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Models;

use App\Domains\Complaint\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Enquête de satisfaction du plaignant à la clôture d'une plainte (EF-08).
 */
class SatisfactionSurvey extends Model
{
    protected $fillable = [
        'complaint_id',
        'overall_rating',
        'timeliness_rating',
        'staff_rating',
        'resolution_rating',
        'would_recommend',
        'comment',
        'channel',
        'collected_by',
        'submitted_at',
    ];

    protected $casts = [
        'overall_rating'    => 'integer',
        'timeliness_rating' => 'integer',
        'staff_rating'      => 'integer',
        'resolution_rating' => 'integer',
        'would_recommend'   => 'boolean',
        'submitted_at'      => 'datetime',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
