<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PermissionRole extends Pivot
{
    public $incrementing = true;

    protected $table = 'permission_roles';

    protected $fillable = ['role_id', 'permission_id'];
}
