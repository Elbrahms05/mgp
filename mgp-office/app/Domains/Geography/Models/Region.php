<?php

declare(strict_types=1);

namespace App\Domains\Geography\Models;

use App\Domains\Site\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Region extends Model
{
    protected $fillable = ['code', 'name'];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function communes(): HasManyThrough
    {
        return $this->hasManyThrough(Commune::class, Department::class);
    }
    public function localities(): HasManyThrough
    {
        return $this->hasManyThrough(Locality::class, Commune::class);
    }

    public function sites(): HasManyThrough
    {
        return $this->hasManyThrough(Site::class, Locality::class);
    }

}
