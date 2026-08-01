<?php

declare(strict_types=1);

namespace App\Domains\Committee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeMember extends Model
{
    protected $fillable = [
        'committee_id',
        'full_name',
        'role',
        'gender',
        'phone',
        'election_mode',
        'is_vbg_focal_point',
        'is_active',
    ];

    protected $casts = [
        'is_vbg_focal_point' => 'boolean',
        'is_active'          => 'boolean',
    ];

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }
}
