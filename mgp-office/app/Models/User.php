<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;
    use LogsActivity;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_scope_type',
        'role_scope_id',
        'phone',
        'locale',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'last_login_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'two_factor_enabled'     => 'boolean',
            'two_factor_confirmed_at'=> 'datetime',
            'last_login_at'          => 'datetime',
            'is_active'              => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontLogIfAttributesChangedOnly(['last_login_at'])
            ->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
