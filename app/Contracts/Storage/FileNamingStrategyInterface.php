<?php

declare(strict_types=1);

namespace App\Contracts\Storage;

use Illuminate\Http\UploadedFile;

interface FileNamingStrategyInterface
{
    /**
     * Generate a secure, unique, and highly standardized filename.
     *
     * @param string $originalName The business-oriented name (e.g., User Full Name).
     * @param UploadedFile $file The physical uploaded file instance.
     * @return string The finalized safe filename with extension.
     */
    public function generate(string $originalName, UploadedFile $file): string;
}
