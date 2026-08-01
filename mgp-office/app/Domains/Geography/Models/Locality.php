<?php

declare(strict_types=1);

namespace App\Domains\Geography\Models;

use App\Domains\Site\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Locality extends Model
{
    protected $fillable = ['commune_id', 'code', 'name'];

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
