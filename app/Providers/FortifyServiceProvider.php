<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Action Required: Verify Your Dental Clinic Account')
                ->greeting('Welcome to Dental Clinic Management Platform!')
                ->line('Thank you for joining our professional medical network. To ensure the security of clinical data and activate your administrative dashboard, please verify your identity.')
                ->action('Activate & Verify Account', $url)
                ->line('This secure verification link will expire in 60 minutes for your security.')
                ->line('If you did not initiate this registration, please disregard this email or contact our system administrator.')
                ->salutation('Best Regards,' . "\n" . 'Dental Clinic Operations Team');
        });

        // 2. تخصيص رسالة استعادة كلمة المرور (Password Reset Email)
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            // بناء رابط الاستعادة الموجه لصفحة الواجهة الأمامية
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Security Alert: Reset Your Password Request')
                ->greeting('Hello,')
                ->line('We received a formal request to reset the password associated with your Dental Clinic account.')
                ->action('Reset Secure Password', $url)
                ->line('This recovery link is strictly valid for 60 minutes. If you did not make this request, your account credentials remain safe and no further action is required.')
                ->salutation('Security & Integrity Team,' . "\n" . 'Dental Clinic Platform');
        });

        // 3. ربط واجهات استعادة كلمة المرور مع Inertia
        Fortify::requestPasswordResetLinkView(function () {
            return inertia('Auth/ForgotPassword');
        });

        Fortify::resetPasswordView(function ($request) {
            return inertia('Auth/ResetPassword', [
                'token' => $request->route('token'),
                'email' => $request->email,
            ]);
        });
    }
}
