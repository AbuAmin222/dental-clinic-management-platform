<?php

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SpecializationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = [
            'General Dentistry',
            'Orthodontics',
            'Endodontics',
            'Periodontics',
            'Pediatric Dentistry',
            'Oral and Maxillofacial Surgery'
        ];

        foreach ($specialties as $name) {
            Specialization::firstOrCreate([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Expert services in $name."
            ]);
        }
        Cache::forget('clinic.specializations');
    }
}
