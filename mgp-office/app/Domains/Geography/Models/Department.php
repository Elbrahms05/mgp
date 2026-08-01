<?php

declare(strict_types=1);

namespace App\Domains\Geography\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['region_id', 'code', 'name'];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class);
    }
}
