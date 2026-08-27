<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserPermission extends Pivot
{
    public $incrementing = true;

    protected $table = 'user_permissions';

    protected $fillable = ['user_id', 'permission_id', 'granted_by'];
}
