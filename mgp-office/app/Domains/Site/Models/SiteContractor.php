<?php

declare(strict_types=1);

namespace App\Domains\Site\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteContractor extends Model
{
    protected $fillable = [
        'site_id',
        'name',
        'contract_number',
        'contract_amount',
        'start_date',
        'end_date',
        'is_active',
        'complaints_count',
    ];

    protected $casts = [
        'contract_amount'  => 'decimal:2',
        'start_date'       => 'date',
        'end_date'         => 'date',
        'is_active'        => 'boolean',
        'complaints_count' => 'integer',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
