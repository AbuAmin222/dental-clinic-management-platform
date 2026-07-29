<?php

declare(strict_types=1);

namespace App\Exceptions\Storage;

use RuntimeException;

/**
 * Base Storage Domain Exception.
 *
 * Serves as the high-level marker exception for all filesystem and storage-related failures.
 * Catching this class allows intercepting any storage error regardless of specific type.
 */
class StorageException extends RuntimeException {}
