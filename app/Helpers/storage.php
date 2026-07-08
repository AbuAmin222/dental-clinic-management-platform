<?php

use App\Services\FileStorageService;

if (!function_exists('storage_engine')) {
    /**
     * Access the Global File Storage Service Engine.
     */
    function storage_engine(): FileStorageService
    {
        return app(FileStorageService::class);
    }
}
