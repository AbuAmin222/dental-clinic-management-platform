<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReceptionistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Receptionist::factory(3)->create();
    }
}
