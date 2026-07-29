<?php

declare(strict_types=1);

namespace App\Strategies\Storage;

use App\Contracts\Storage\FileNamingStrategyInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class UuidFileNamingStrategy implements FileNamingStrategyInterface
{
    /**
     * Generates a safe, sanitized, UUID-backed unique filename.
     *
     * @param string $originalName
     * @param UploadedFile $file
     * @return string
     */
    public function generate(string $originalName, UploadedFile $file): string
    {
        $slugified = Str::slug($originalName, '_');
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();

        $extension = $extension ? '.' . $extension : '';

        return sprintf('%s-%s%s', $slugified, $uuid, $extension);
    }
}
