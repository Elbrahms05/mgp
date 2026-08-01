<?php

declare(strict_types=1);

namespace App\Domains\Complaint\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplaintType extends Model
{
    protected $fillable = ['code', 'label', 'is_sensitive', 'sort_order'];

    protected $casts = [
        'is_sensitive' => 'boolean',
        'sort_order'   => 'integer',
    ];

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
