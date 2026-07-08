<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Pricing::factory(15)->create();
    }
}
