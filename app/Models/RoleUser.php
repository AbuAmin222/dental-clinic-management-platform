<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleUser extends Pivot
{
    public $incrementing = true;

    protected $table = 'role_users';

    protected $fillable = ['user_id', 'role_id', 'is_primary'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];
}
