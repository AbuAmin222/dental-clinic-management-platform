<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * * Bootstraps customized cascading account deletion strategies and adjusts
     * asset loader prefetch limits for superior dashboard transitions.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configurePermissions();

        // Bind our high-integrity, decoupled user cascading cleanup handler
        Jetstream::deleteUsersUsing(DeleteUser::class);

        // Prefetch configuration for lightning-fast assets synchronization
        Vite::prefetch(concurrency: 3);
    }

    /**
     * Configure the strict state permission parameters accessible to system API tokens.
     *
     * @return void
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
