<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Contracts\Storage\FileStorageServiceInterface;

if (! function_exists('storage_engine')) {
    /**
     * Access the unified Global File Storage Service Engine instance.
     * This helper resolves the abstract decoupled storage engine contract from Laravel's IoC container.
     *
     * @return \App\Contracts\Storage\FileStorageServiceInterface
     */
    function storage_engine(): FileStorageServiceInterface
    {
        return app(FileStorageServiceInterface::class);
    }
}
