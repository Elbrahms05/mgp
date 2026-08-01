<?php

declare(strict_types=1);

namespace App\Domains\Form\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'form_id',
        'form_type',
        'name',
        'description',
        'complaint_type_id',
        'is_active',
        'active_regions',
        'current_version',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'active_regions' => 'array',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(FormVersion::class);
    }
}
