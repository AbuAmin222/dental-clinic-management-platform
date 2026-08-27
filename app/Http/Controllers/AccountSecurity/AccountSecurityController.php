<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountSecurity;

use App\Exceptions\BusinessRuleViolationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccountSecurity\UpdateForcedPasswordRequest;
use App\Http\Requests\AccountSecurity\VerifyAccountCodeRequest;
use App\Services\Security\AccountVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * الواجهة الوحيدة التي يستطيع حساب "يتطلب استكمال إجراءات أمان" الوصول إليها — يفرض ذلك
 * EnsureAccountSecurityCompleted على كل مسار آخر. Slim Controller بالكامل: كل منطق العمل
 * الفعلي (توليد/تحقق الرمز، تغيير كلمة المرور) في AccountVerificationService.
 */
class AccountSecurityController extends Controller
{
    public function __construct(
        private readonly AccountVerificationService $accountVerificationService,
    ) {}

    public function show(Request $request): InertiaResponse
    {
        $user = $request->user();

        return Inertia::render('Auth/AccountSecurity', [
            'mustChangePassword' => (bool) $user->must_change_password,
            'phoneVerified' => $user->phone_verified_at !== null,
        ]);
    }

    public function updatePassword(UpdateForcedPasswordRequest $request): RedirectResponse
    {
        $this->accountVerificationService->completeForcedPasswordChange(
            $request->user(),
            $request->validated('password'),
        );

        return redirect()->route('account-security.show')
            ->with('success', __('Password updated successfully.'));
    }

    public function verifyCode(VerifyAccountCodeRequest $request): RedirectResponse
    {
        try {
            $this->accountVerificationService->verifyCode($request->user(), $request->validated('code'));
        } catch (BusinessRuleViolationException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', __('Account verified successfully.'));
    }

    /**
     * إعادة إرسال الرمز — محدودة بمعدل صريح (Rate Limit) منعاً لإساءة الاستخدام واستنزاف
     * حصة إرسال البريد، القيمة من config('clinic.account_security.verification_code_resend_max_per_hour').
     */
    public function resendCode(Request $request): RedirectResponse
    {
        $user = $request->user();
        $maxPerHour = (int) config('clinic.account_security.verification_code_resend_max_per_hour', 5);
        $rateLimitKey = 'verification-code-resend:' . $user->id;
        // dd(RateLimiter::tooManyAttempts($rateLimitKey, $maxPerHour));

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxPerHour)) {
            return back()->withErrors([
                'code' => __('Too many code requests. Please try again later.'),
            ]);
        }

        RateLimiter::hit($rateLimitKey, 3600);

        $this->accountVerificationService->generateAndSendVerificationCode($user);

        return back()->with('success', __('A new verification code has been sent.'));
    }
}
