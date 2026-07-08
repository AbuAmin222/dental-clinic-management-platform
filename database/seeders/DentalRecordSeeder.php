<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DentalRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\DentalRecord::factory(30)->create();
    }
}
