<?php

declare(strict_types=1);

namespace App\Domains\Site\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteType extends Model
{
    protected $fillable = ['code', 'label', 'component'];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
