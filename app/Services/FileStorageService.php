<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Upload a file with a secure, unique UUID name.
     */
    public function upload(string $name, UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        // Clean directory path string
        $directory = trim($directory, '/');

        // Generate a completely random UUID name to preserve anonymity and security
        $fileName = Str::slug($name, '_') . '-' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Store and return the relative path
        return $file->storeAs($directory, $fileName, $disk);
    }

    /**
     * Resolve the fully qualified public URL for a given relative path.
     */
    public function url(?string $path, string $disk = 'public', ?string $fallback = null): ?string
    {
        if (!$path) {
            return $fallback;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $diskProvider */
        $diskProvider = Storage::disk($disk);

        return $diskProvider->url($path);
    }

    /**
     * Safely delete an existing file from the disk if it exists.
     */
    public function delete(?string $path, string $disk = 'public'): bool
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Replace an old file with a new one (Handles upload + old file deletion automatically).
     */
    public function update(string $name, UploadedFile $newFile, ?string $oldPath, string $directory, string $disk = 'public'): string
    {
        // 1. Delete the old file first if it exists
        $this->delete($oldPath, $disk);

        // 2. Upload the new file
        return $this->upload($name, $newFile, $directory, $disk);
    }
}
