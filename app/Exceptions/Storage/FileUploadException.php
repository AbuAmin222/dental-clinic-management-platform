<?php

declare(strict_types=1);

namespace App\Exceptions\Storage;

/**
 * Domain Exception thrown specifically during file upload failures.
 *
 * Encapsulates permission errors, disk write failures, or illegal MIME-type rejections.
 */
class FileUploadException extends StorageException {}
