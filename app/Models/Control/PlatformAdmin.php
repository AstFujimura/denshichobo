<?php

namespace App\Models\Control;

use Illuminate\Foundation\Auth\User as Authenticatable;

class PlatformAdmin extends Authenticatable
{
    protected $connection = 'control';

    protected $table = 'platform_admins';

    protected $fillable = [
        'name',
        'display_name',
        'email',
        'password',
        'is_active',
        'can_provision',
        'can_impersonate',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'can_provision' => 'boolean',
        'can_impersonate' => 'boolean',
        'last_login_at' => 'datetime',
    ];
}
