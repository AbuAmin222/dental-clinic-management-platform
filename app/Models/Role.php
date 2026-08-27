<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'profile_type',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_users')->withPivot('is_primary');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_roles')->using(PermissionRole::class);
    }

    /**
     * منح صلاحيات للدور
     */
    public function givePermissionTo(Permission|string ...$permissions): self
    {
        $permissionModels = collect($permissions)->map(function ($permission) {
            return is_string($permission)
                ? Permission::where('name', $permission)->firstOrFail()
                : $permission;
        });

        $this->permissions()->syncWithoutDetaching($permissionModels->pluck('id'));

        return $this;
    }

    /**
     * سحب صلاحيات من الدور
     */
    public function revokePermissionTo(Permission|string ...$permissions): self
    {
        $permissionModels = collect($permissions)->map(function ($permission) {
            return is_string($permission)
                ? Permission::where('name', $permission)->first()
                : $permission;
        })->filter();

        $this->permissions()->detach($permissionModels->pluck('id'));

        return $this;
    }

    /**
     * التحقق من وجود صلاحية محددة لدى الدور
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions->contains('name', $permissionName);
    }
}
