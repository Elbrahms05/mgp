<?php

declare(strict_types=1);

namespace App\Domains\Documentation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageGuide extends Model
{
    protected $fillable = [
        'route_name',
        'page_title',
        'mgp_reference',
        'summary',
        'detailed_guide',
        'warnings',
        'related_glossary',
        'video_url',
        'is_active',
        'display_mode',
        'sort_order',
    ];

    protected $casts = [
        'warnings'        => 'array',
        'related_glossary'=> 'array',
        'is_active'       => 'boolean',
        'sort_order'      => 'integer',
    ];

    public function tooltips(): HasMany
    {
        return $this->hasMany(FieldTooltip::class, 'route_name', 'route_name');
    }
}
