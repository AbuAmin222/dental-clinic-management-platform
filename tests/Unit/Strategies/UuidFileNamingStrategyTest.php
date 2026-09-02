<?php

declare(strict_types=1);

namespace Tests\Unit\Strategies;

use App\Contracts\Storage\FileNamingStrategyInterface;
use App\Strategies\Storage\UuidFileNamingStrategy;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UuidFileNamingStrategyTest extends TestCase
{
    #[Test]
    public function implements_contract(): void
    {
        $strategy = new UuidFileNamingStrategy();

        $this->assertInstanceOf(FileNamingStrategyInterface::class, $strategy);
    }

    #[Test]
    public function generates_filename_with_slugified_name_and_uuid(): void
    {
        $strategy = new UuidFileNamingStrategy();
        $file = $this->createConfiguredMock(UploadedFile::class, [
            'getClientOriginalExtension' => 'jpg',
        ]);

        $result = $strategy->generate('Jane Smith', $file);

        $this->assertStringStartsWith('jane_smith-', $result);
        $this->assertStringEndsWith('.jpg', $result);
    }

    #[Test]
    public function slugs_spaces_to_underscores(): void
    {
        $strategy = new UuidFileNamingStrategy();
        $file = $this->createConfiguredMock(UploadedFile::class, [
            'getClientOriginalExtension' => 'png',
        ]);

        $result = $strategy->generate('John  Doe', $file);

        $this->assertStringStartsWith('john_doe-', $result);
    }

    #[Test]
    public function includes_extension_when_provided(): void
    {
        $strategy = new UuidFileNamingStrategy();
        $file = $this->createConfiguredMock(UploadedFile::class, [
            'getClientOriginalExtension' => 'pdf',
        ]);

        $result = $strategy->generate('Document', $file);

        $this->assertStringEndsWith('.pdf', $result);
    }

    #[Test]
    public function handles_empty_extension(): void
    {
        $strategy = new UuidFileNamingStrategy();
        $file = $this->createConfiguredMock(UploadedFile::class, [
            'getClientOriginalExtension' => '',
        ]);

        $result = $strategy->generate('No Extension', $file);

        $this->assertStringContainsString('no_extension', $result);
    }

    #[Test]
    public function generates_unique_filenames(): void
    {
        $strategy = new UuidFileNamingStrategy();
        $file = $this->createConfiguredMock(UploadedFile::class, [
            'getClientOriginalExtension' => 'jpg',
        ]);

        $result1 = $strategy->generate('Same Name', $file);
        $result2 = $strategy->generate('Same Name', $file);

        $this->assertNotSame($result1, $result2);
    }

    #[Test]
    public function handles_special_characters_in_name(): void
    {
        $strategy = new UuidFileNamingStrategy();
        $file = $this->createConfiguredMock(UploadedFile::class, [
            'getClientOriginalExtension' => 'jpg',
        ]);

        $result = $strategy->generate('Test @#$% Name!', $file);

        $this->assertStringStartsWith('test_at_name-', $result);
    }
}
