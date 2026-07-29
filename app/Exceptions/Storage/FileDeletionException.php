<?php

declare(strict_types=1);

namespace App\Exceptions\Storage;

/**
 * Domain Exception thrown specifically when physical file purging fails.
 *
 * Encapsulates missing file errors, locked system resources, or storage driver failures during deletion.
 */
class FileDeletionException extends StorageException {}
