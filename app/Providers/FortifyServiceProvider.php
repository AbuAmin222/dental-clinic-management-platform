<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\LoginResponse;
use App\Models\User;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class FortifyServiceProvider
 *
 * Identity & Access Management (IAM) Identity Bootstrapper for the Dental Clinic Application (DCA).
 * This provider orchestrates Laravel Fortify authentication services with production-ready optimizations,
 * leveraging strict compile-time types, asynchronous decoupled notifications, and dynamic multi-channel
 * lookup engines tailored for extreme scalability and elite performance.
 *
 * @package App\Providers
 */
class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application authentication services.
     *
     * Injects core contract implementations into the IoC service container. Customizes the global
     * authentication response workflow by binding a multi-role dynamic redirection routing layer.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the unified custom dynamic redirect response engine for high architectural flexibility
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application authentication services.
     *
     * Initializes the defensive rate-limiting architecture, binds custom multi-channel (email/username)
     * authentication pipelines, hooks deferred queue notification listeners, and maps front-end
     * single-page interactive views to Inertia components.
     *
     * @return void
     */
    public function boot(): void
    {
        // Section 1: Register Core Fortify Decoupled Action Processors (SRP Compliance)
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Section 2: Decoupled Async Notification Pipeline Overrides
        // Intercepts default synchronous mailing to offload transaction overhead onto background worker queues.
        ResetPassword::toMailUsing(static function (User $user, string $token): void {
            $user->notify(new QueuedResetPassword($token));
        });

        VerifyEmail::toMailUsing(static function (User $user): void {
            $user->notify(new QueuedVerifyEmail());
        });

        // Section 3: High-Security Infrastructure Brute-Force Rate Limiters
        RateLimiter::for('login', static function (Request $request): Limit {
            // Unify credentials to extract a single clean lowercase lookup target irrespective of using email or username
            $loginValue = Str::transliterate(
                Str::lower((string) (
                    $request->input('email') ??
                    $request->input('username') ??
                    $request->input('login')
                ))
            );

            return Limit::perMinute(5)->by($loginValue . '|' . $request->ip());
        });

        RateLimiter::for('two-factor', static function (Request $request): Limit {
            $sessionId = (string) $request->session()->get('login.id');

            return Limit::perMinute(5)->by($sessionId . '|' . $request->ip());
        });

        // Section 4: High-Performance Seamless Dual-Credential Authentication (Email or Username)
        Fortify::authenticateUsing(static function (Request $request): ?User {
            $loginTarget = (string) (
                $request->input('email') ??
                $request->input('username') ??
                $request->input('login')
            );

            if (empty($loginTarget)) {
                return null;
            }

            // High-performance indexed database lookup accommodating either email or username natively
            $user = User::where('email', $loginTarget)
                ->orWhere('username', $loginTarget)
                ->first();

            if ($user && Hash::check((string) $request->input('password'), $user->password)) {
                return $user;
            }

            return null;
        });

        // Section 5: Bind Authentication Front-End View Interactivity via Inertia
        Fortify::loginView(static function (): InertiaResponse {
            return Inertia::render('Auth/Login');
        });

        Fortify::requestPasswordResetLinkView(static function (): InertiaResponse {
            return Inertia::render('Auth/ForgotPassword');
        });

        Fortify::resetPasswordView(static function (Request $request): InertiaResponse {
            return Inertia::render('Auth/ResetPassword', [
                'token' => (string) $request->route('token'),
                'email' => (string) $request->query('email'),
            ]);
        });
    }
}
