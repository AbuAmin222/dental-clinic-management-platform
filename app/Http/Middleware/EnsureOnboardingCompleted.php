<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * @var array<string, string> role => اسم علاقة الـ Profile على User (financial() مثلاً)
     */
    private const ROLE_PROFILE_MAP = [
        // 'doctor'       => 'doctor',       // بانتظار is_profile_completed + Onboarding الخاص بالطبيب
        // 'patient'      => 'patient',      // بانتظار is_profile_completed + Onboarding الخاص بالمريض
        // 'receptionist' => 'receptionist', // بانتظار is_profile_completed + Onboarding الخاص بالاستقبال
        // ملاحظة: الخريطة أعلاه معلَّقة عمداً — الأربعة Registrars تطلب هذا الـ Middleware
        // مسبقاً لكن هذه الأدوار الثلاثة ليس لها عمود is_profile_completed أو صفحة
        // Onboarding فعلية بعد؛ تفعيلها الآن كان سيُحوّل كل مستخدم من هذه الأدوار لمسار
        // Route غير موجود (RouteNotFoundException) عند أول طلب.
    ];

    private const FINANCIAL_MAP = [
        'financial' => 'financial',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $map = self::FINANCIAL_MAP + self::ROLE_PROFILE_MAP;

        foreach ($map as $role => $profileRelation) {
            if (! $user->hasRole($role)) {
                continue;
            }

            $profile = $user->{$profileRelation};

            $isComplete = $profile !== null && (bool) ($profile->is_profile_completed ?? false);

            if (! $isComplete && ! $request->routeIs("{$role}.onboarding.*")) {
                return redirect()->route("{$role}.onboarding.show");
            }

            break;
        }

        return $next($request);
    }
}
