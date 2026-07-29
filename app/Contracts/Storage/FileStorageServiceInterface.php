<?php

declare(strict_types=1);

namespace App\Contracts\Storage;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

interface FileStorageServiceInterface
{
    /**
     * Securely upload a file asset to a specific storage disk directory.
     *
     * @param string $name Reference name for the asset.
     * @param UploadedFile $file The uploaded file instance.
     * @param string $directory Destination path directory.
     * @param string $disk The target filesystem storage disk.
     * @return string The relative path to the stored file.
     *
     * @throws \App\Exceptions\Storage\FileUploadException If the upload operation fails.
     */
    public function upload(string $name, UploadedFile $file, string $directory, string $disk = 'public'): string;

    /**
     * Resolve the absolute URL for the given file asset relative path.
     *
     * @param string|null $path Relative storage path.
     * @param string $disk The target filesystem storage disk.
     * @param string|null $fallback Fallback URL if the path is missing or invalid.
     * @return string|null The fully qualified URL or fallback.
     */
    public function url(?string $path, string $disk = 'public', ?string $fallback = null): ?string;

    /**
     * Safely purge a file from physical storage.
     *
     * @param string|null $path The relative file path to delete.
     * @param string $disk The target filesystem storage disk.
     * @return bool True on successful deletion, false otherwise.
     *
     * @throws \App\Exceptions\Storage\FileDeletionException If an active storage error occurs.
     */
    public function delete(?string $path, string $disk = 'public'): bool;

    /**
     * Perform an atomic safe-replacement of an existing asset with a new upload.
     * Highly robust: Uploads first and only deletes the old file on success.
     *
     * @param string $name Reference name for the asset.
     * @param UploadedFile $newFile The newly uploaded file instance.
     * @param string|null $oldPath The existing file relative path to replace.
     * @param string $directory Destination path directory.
     * @param string $disk The target filesystem storage disk.
     * @return string The relative path to the newly stored file.
     *
     * @throws \App\Exceptions\Storage\FileUploadException If the new upload fails.
     */
    public function update(
        string $name,
        UploadedFile $newFile,
        ?string $oldPath,
        string $directory,
        string $disk = 'public'
    ): string;

    /**
     * Check whether a file exists at the specified relative path.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function exists(string $path, string $disk = 'local'): bool;

    /**
     * Generate an HTTP file download or stream response for a given file.
     *
     * @param string $path
     * @param string $disk
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(string $path, string $disk = 'local'): Response;
}
