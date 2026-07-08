<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $depts = ['Front Desk', 'Billing', 'Clinical Operations', 'Radiology'];

        foreach ($depts as $name) {
            Department::firstOrCreate([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Department responsible for $name",
                'is_active' => true
            ]);
        }
        Cache::forget('clinic.departments');
    }
}
