<?php

declare(strict_types=1);

namespace App\Domains\Documentation\Models;

use Illuminate\Database\Eloquent\Model;

class FieldTooltip extends Model
{
    protected $fillable = [
        'route_name',
        'field_name',
        'tooltip_text',
        'mgp_reference',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
