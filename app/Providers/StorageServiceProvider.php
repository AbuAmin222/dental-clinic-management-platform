<?php

namespace App\Providers;

use App\Contracts\Storage\FileNamingStrategyInterface;
use App\Contracts\Storage\FileStorageServiceInterface;
use App\Services\Storage\FileStorageService;
use App\Strategies\Storage\UuidFileNamingStrategy;
use Illuminate\Support\ServiceProvider;

class StorageServiceProvider extends ServiceProvider
{
    /**
     * Register storage domain services.
     */
    public function register(): void
    {
        $this->app->bind(FileNamingStrategyInterface::class, UuidFileNamingStrategy::class);

        $this->app->singleton(FileStorageServiceInterface::class, function ($app) {
            return new FileStorageService(
                $app->make(FileNamingStrategyInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
