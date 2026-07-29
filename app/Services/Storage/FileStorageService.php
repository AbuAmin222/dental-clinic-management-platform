<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Contracts\Storage\FileNamingStrategyInterface;
use App\Contracts\Storage\FileStorageServiceInterface;
use App\Exceptions\Storage\FileDeletionException;
use App\Exceptions\Storage\FileUploadException;
use App\Exceptions\Storage\StorageException;
use App\Strategies\Storage\UuidFileNamingStrategy;
use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FileStorageService implements FileStorageServiceInterface
{
    /**
     * The active file naming strategy contract.
     */
    protected FileNamingStrategyInterface $namingStrategy;

    /**
     * FileStorageService constructor.
     * Employs dynamic constructor dependency injection for pluggable naming structures.
     */
    public function __construct(?FileNamingStrategyInterface $namingStrategy = null)
    {
        $this->namingStrategy = $namingStrategy ?? new UuidFileNamingStrategy();
    }


    /**
     * {@inheritdoc}
     */
    public function upload(string $name, UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        try {
            $directory = trim($directory, '/');
            $fileName = $this->namingStrategy->generate($name, $file);

            $extension = strtolower($file->getClientOriginalExtension());
            if (in_array($extension, ['php', 'phtml', 'phar', 'phps', 'sh', 'bat'], true)) {
                throw new FileUploadException('Security Restriction: Executable scripts cannot be written to storage.');
            }

            $storedPath = $file->storeAs($directory, $fileName, $disk);

            if ($storedPath === false) {
                throw new FileUploadException("Filesystem failed to write asset to configured disk [{$disk}].");
            }

            return $storedPath;
        } catch (Exception $e) {
            Log::error('File Storage upload failed', [
                'name' => $name,
                'directory' => $directory,
                'disk' => $disk,
                'exception' => $e->getMessage()
            ]);

            if ($e instanceof FileUploadException) {
                throw $e;
            }

            throw new FileUploadException(
                "An unexpected error occurred during storage upload to disk [{$disk}]: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function url(?string $path, string $disk = 'public', ?string $fallback = null): ?string
    {
        if (!$path) {
            return $fallback;
        }

        try {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $diskProvider */
            $diskProvider = Storage::disk($disk);
            return $diskProvider->url($path);
        } catch (Exception $e) {
            Log::warning("Could not resolve asset absolute URL for path [{$path}] on disk [{$disk}]", [
                'exception' => $e->getMessage()
            ]);
            return $fallback;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(?string $path, string $disk = 'public'): bool
    {
        if (!$path) {
            return false;
        }

        try {
            if (Storage::disk($disk)->exists($path)) {
                $deleted = Storage::disk($disk)->delete($path);
                if (!$deleted) {
                    throw new FileDeletionException("Target file [{$path}] exists, but storage driver returned failure status on deletion.");
                }
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error("File deletion routine failed for path [{$path}] on disk [{$disk}]", [
                'exception' => $e->getMessage()
            ]);

            if ($e instanceof FileDeletionException) {
                throw $e;
            }

            throw new FileDeletionException(
                "FileSystem error encountered while deleting file [{$path}]: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * HIGH ROBUSTNESS FIX:
     * This method prevents data-loss by executing the new upload first.
     * The old file is strictly preserved until the new upload has successfully finalized.
     */
    public function update(
        string $name,
        UploadedFile $newFile,
        ?string $oldPath,
        string $directory,
        string $disk = 'public'
    ): string {
        $newPath = $this->upload($name, $newFile, $directory, $disk);

        if ($oldPath) {
            try {
                $this->delete($oldPath, $disk);
            } catch (Exception $e) {
                Log::warning("Orphaned asset cleanup omitted during replacement routine for [{$oldPath}]", [
                    'exception' => $e->getMessage()
                ]);
            }
        }

        return $newPath;
    }

    /**
     * Runtime naming strategy setter to facilitate extreme Open-Closed flexibility.
     */
    public function setNamingStrategy(FileNamingStrategyInterface $namingStrategy): self
    {
        $this->namingStrategy = $namingStrategy;
        return $this;
    }

    /**
     * Check if a file exists on the given storage disk.
     */
    public function exists(string $path, string $disk = 'local'): bool
    {
        /** @var FilesystemAdapter $storageDisk */
        $storageDisk = Storage::disk($disk);

        return $storageDisk->exists($path);
    }

    /**
     * Generate an HTTP binary/stream response for a file.
     *
     * @throws StorageException
     */
    public function response(string $path, string $disk = 'local'): Response
    {
        /** @var FilesystemAdapter $storageDisk */
        $storageDisk = Storage::disk($disk);

        if (! $storageDisk->exists($path)) {
            throw new StorageException('Target storage file does not exist: ' . $path);
        }

        return $storageDisk->response($path);
    }
}
