<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Security\AccountVerificationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يفرض متطلبات الأمان (§2.ج من الوثيقة المعمارية) على أي حساب لا يزال بحاجة لتغيير كلمة
 * المرور الإجبارية أو توثيق رقم الهاتف — يُحوِّل لصفحة استكمال الأمان بدل أي صفحة أخرى،
 * تماماً بنفس نمط `EnsureOnboardingCompleted`/`EnsureUserIsActive` الموجودَين مسبقاً.
 */
class EnsureAccountSecurityCompleted
{
    private const EXEMPT_ROUTE_NAMES = ['account-security.*', 'logout'];

    public function __construct(
        private readonly AccountVerificationService $accountVerificationService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($this->accountVerificationService->requiresSecurityCompletion($user)
            && ! $request->routeIs(...self::EXEMPT_ROUTE_NAMES)) {
            return redirect()->route('account-security.show');
        }

        return $next($request);
    }
}
