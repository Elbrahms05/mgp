<?php

declare(strict_types=1);

namespace App\Domains\Company\Models;

use App\Domains\Site\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'name',
        'number_registre',
        'phone',
        'email',
        'address',
        'website',
        'sector',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relation: Companies have many Sites through pivot table
     */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'company_site')
            ->withPivot('responsible_name', 'responsible_phone', 'responsible_email')
            ->withTimestamps();
    }
}
