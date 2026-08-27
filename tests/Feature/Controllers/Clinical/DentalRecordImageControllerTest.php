<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Clinical;

use App\Models\DentalRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DentalRecordImageControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function show_returns_404_when_xray_path_is_empty(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $record = DentalRecord::factory()->create(['xray_image_path' => null]);

        $response = $this->get("/dental-records/{$record->id}/xray");

        $response->assertNotFound();
    }

    #[Test]
    public function show_returns_404_when_file_does_not_exist(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $record = DentalRecord::factory()->create(['xray_image_path' => 'nonexistent/file.jpg']);

        $response = $this->get("/dental-records/{$record->id}/xray");

        $response->assertNotFound();
    }
}
