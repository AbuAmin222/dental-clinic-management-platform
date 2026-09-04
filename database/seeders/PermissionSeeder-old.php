<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permissions\PermissionRegistry;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionRegistry::all() as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission->value],
                ['display_name' => $permission->label(), 'group' => $permission->group()],
            );
        }
    }
}
