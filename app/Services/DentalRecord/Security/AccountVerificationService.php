<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Exceptions\BusinessRuleViolationException;
use App\Models\User;
use App\Notifications\PatientVerificationCodeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * يطبّق الشطر الثاني من §2.ج في الوثيقة المعمارية: منع الوصول لأي خدمة عند أول دخول
 * لمريض أنشأه الاستقبال، حتى يغيّر كلمة المرور ويُدخل رمز تأكيد مُرسَل له.
 *
 * منطق العمل مُركَّز هنا حصراً (وليس في الـ Middleware أو الـ Controller) — نفس مبدأ
 * فصل المسؤوليات المطبَّق على InvoiceService/SalaryPaymentService: الـ Middleware يقرر
 * "هل يجب الحظر؟"، والـ Controller يستقبل الطلب فقط، وهذه الخدمة تنفّذ القواعد الفعلية.
 */
class AccountVerificationService
{
    /**
     * يُنشئ رمز تحقق جديد، يُخزِّنه مُجزَّأً (Hash) فقط، ويُرسل النسخة الخام عبر إشعار
     * مُصفوف (Queued) — القيمة الخام لا تُخزَّن في قاعدة البيانات إطلاقاً بعد هذه النقطة.
     */
    public function generateAndSendVerificationCode(User $user): void
    {
        $codeLength = (int) config('clinic.account_security.verification_code_length', 6);
        $expiryMinutes = (int) config('clinic.account_security.verification_code_expiry_minutes', 15);

        $rawCode = (string) random_int(
            (int) str_pad('1', $codeLength, '0'),
            (int) str_pad('9', $codeLength, '9'),
        );

        $user->forceFill([
            'phone_verification_code' => Hash::make($rawCode),
            'phone_verification_code_expires_at' => now()->addMinutes($expiryMinutes),
        ])->save();

        $user->notify(new PatientVerificationCodeNotification($rawCode, $expiryMinutes));
    }

    /**
     * @throws BusinessRuleViolationException إذا كان الرمز خاطئاً أو منتهي الصلاحية.
     */
    public function verifyCode(User $user, string $submittedCode): void
    {
        if (blank($user->phone_verification_code) || $user->phone_verification_code_expires_at === null) {
            throw new BusinessRuleViolationException(__('No verification code was requested. Please request a new code.'));
        }

        if (now()->greaterThan($user->phone_verification_code_expires_at)) {
            throw new BusinessRuleViolationException(__('This verification code has expired. Please request a new one.'));
        }

        if (! Hash::check($submittedCode, $user->phone_verification_code)) {
            throw new BusinessRuleViolationException(__('The verification code you entered is incorrect.'));
        }

        $user->forceFill([
            'phone_verified_at' => now(),
            'phone_verification_code' => null,
            'phone_verification_code_expires_at' => null,
        ])->save();
    }

    /**
     * يُغيّر كلمة المرور ويرفع علم `must_change_password` — يُستدعى من خطوة تغيير كلمة
     * المرور الإجبارية، منفصل عمداً عن verifyCode() (المستخدم قد يُنجز الخطوتين بترتيب
     * مختلف، ولا يجوز افتراض تسلسل واحد ثابت بينهما داخل هذه الخدمة).
     */
    public function completeForcedPasswordChange(User $user, string $newPassword): void
    {
        $user->forceFill([
            'password' => Hash::make($newPassword),
            'must_change_password' => false,
        ])->save();
    }

    /**
     * الحساب لا يزال يتطلب استكمال متطلبات الأمان (يمنع الوصول لأي خدمة أخرى) طالما لم
     * يُغيَّر كلمة المرور الإجبارية أو لم يُوثَّق رقم الهاتف بعد.
     */
    public function requiresSecurityCompletion(User $user): bool
    {
        return $user->must_change_password || $user->phone_verified_at === null;
    }
}
