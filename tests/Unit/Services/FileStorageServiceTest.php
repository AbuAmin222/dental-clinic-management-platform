<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\Storage\FileDeletionException;
use App\Exceptions\Storage\FileUploadException;
use App\Services\Storage\FileStorageService;
use App\Strategies\Storage\UuidFileNamingStrategy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FileStorageServiceTest extends TestCase
{
    private FileStorageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->service = new FileStorageService();
    }

    #[Test]
    public function upload_stores_file_and_returns_path(): void
    {
        $file = UploadedFile::fake()->create('test.jpg', 100);

        $path = $this->service->upload('Test Name', $file, 'uploads/profiles');

        $this->assertNotEmpty($path);
        $this->assertStringContainsString('uploads/profiles', $path);
        $this->assertTrue(Storage::disk('public')->exists($path));
    }

    #[Test]
    public function upload_uses_default_uuid_naming_strategy(): void
    {
        $file = UploadedFile::fake()->create('test.png', 100);

        $path = $this->service->upload('John Doe', $file, 'uploads/images');

        $this->assertStringContainsString('john_doe', $path);
        $this->assertStringEndsWith('.png', $path);
    }

    #[Test]
    public function upload_rejects_executable_files(): void
    {
        $file = UploadedFile::fake()->create('malicious.php', 100);

        $this->expectException(FileUploadException::class);
        $this->expectExceptionMessage('Security Restriction');

        $this->service->upload('Malicious', $file, 'uploads/scripts');
    }

    #[Test]
    public function upload_rejects_shell_scripts(): void
    {
        $file = UploadedFile::fake()->create('script.sh', 100);

        $this->expectException(FileUploadException::class);

        $this->service->upload('Script', $file, 'uploads');
    }

    #[Test]
    public function upload_rejects_phtml_files(): void
    {
        $file = UploadedFile::fake()->create('malicious.phtml', 100);

        $this->expectException(FileUploadException::class);

        $this->service->upload('Malicious', $file, 'uploads');
    }

    #[Test]
    public function url_returns_fallback_when_path_is_null(): void
    {
        $this->assertNull($this->service->url(null, 'public', 'https://example.com/fallback.jpg'));
    }

    #[Test]
    public function url_returns_fallback_when_path_is_empty_string(): void
    {
        $this->assertSame('https://example.com/fallback.jpg', $this->service->url('', 'public', 'https://example.com/fallback.jpg'));
    }

    #[Test]
    public function url_returns_storage_url_when_file_exists(): void
    {
        $path = $this->service->upload('Test', UploadedFile::fake()->create('doc.jpg', 100), 'test');

        $url = $this->service->url($path, 'public');

        $this->assertNotNull($url);
        $this->assertStringContainsString('test', $url);
    }

    #[Test]
    public function url_returns_fallback_on_storage_exception(): void
    {
        $url = $this->service->url('nonexistent/path.jpg', 'local', 'https://example.com/fallback.jpg');

        $this->assertSame('https://example.com/fallback.jpg', $url);
    }

    #[Test]
    public function delete_returns_false_for_null_path(): void
    {
        $this->assertFalse($this->service->delete(null));
    }

    #[Test]
    public function delete_returns_false_for_empty_path(): void
    {
        $this->assertFalse($this->service->delete(''));
    }

    #[Test]
    public function delete_returns_false_when_file_does_not_exist(): void
    {
        $this->assertFalse($this->service->delete('nonexistent/file.jpg'));
    }

    #[Test]
    public function delete_returns_true_and_removes_file(): void
    {
        $path = $this->service->upload('Test', UploadedFile::fake()->create('doc.jpg', 100), 'test');

        $result = $this->service->delete($path);

        $this->assertTrue($result);
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    #[Test]
    public function update_uploads_new_file_and_deletes_old(): void
    {
        $oldPath = $this->service->upload('Old', UploadedFile::fake()->create('old.jpg', 100), 'test');

        $newPath = $this->service->update(
            'New',
            UploadedFile::fake()->create('new.jpg', 100),
            $oldPath,
            'test'
        );

        $this->assertNotSame($oldPath, $newPath);
        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists($newPath));
    }

    #[Test]
    public function update_without_old_path_still_uploads(): void
    {
        $newPath = $this->service->update(
            'New',
            UploadedFile::fake()->create('new.jpg', 100),
            null,
            'test'
        );

        $this->assertNotEmpty($newPath);
        $this->assertTrue(Storage::disk('public')->exists($newPath));
    }

    #[Test]
    public function set_naming_strategy_changes_strategy_at_runtime(): void
    {
        $customStrategy = $this->createMock(\App\Contracts\Storage\FileNamingStrategyInterface::class);
        $customStrategy->method('generate')->willReturn('custom-filename.jpg');

        $this->service->setNamingStrategy($customStrategy);

        $path = $this->service->upload('Test', UploadedFile::fake()->create('test.jpg', 100), 'test');

        $this->assertStringContainsString('custom-filename.jpg', $path);
    }

    #[Test]
    public function exists_returns_true_for_existing_file(): void
    {
        $path = $this->service->upload('Test', UploadedFile::fake()->create('test.jpg', 100), 'test');

        $this->assertTrue($this->service->exists($path, 'public'));
    }

    #[Test]
    public function exists_returns_false_for_nonexistent_file(): void
    {
        $this->assertFalse($this->service->exists('nonexistent/file.jpg', 'public'));
    }
}
