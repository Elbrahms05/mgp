<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Enregistrement de formation / recyclage d'une personne de confiance VBG (2.5).
 */
class TrustedPersonTraining extends Model
{
    protected $table = 'vbg_trusted_person_trainings';

    protected $fillable = [
        'user_id',
        'trained_at',
        'valid_until',
        'topic',
        'recorded_by',
    ];

    protected $casts = [
        'trained_at'  => 'date',
        'valid_until' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
