<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Contracts\Sms\SmsServiceInterface;

if (! function_exists('sms_engine')) {
    /**
     * الوصول إلى نسخة خدمة SMS الموحّدة العامة (Global SMS Service Engine Instance).
     * يحلّ هذا الـ Helper عقد خدمة SMS المجرّد من حاوية Laravel — تماماً بنفس دور
     * storage_engine() في نطاق التخزين.
     *
     * Access the unified Global SMS Service Engine instance. This helper resolves the
     * abstract SMS service contract from Laravel's IoC container — mirrors the exact
     * role of storage_engine() in the storage domain.
     *
     * @return \App\Contracts\Sms\SmsServiceInterface
     */
    function sms_engine(): SmsServiceInterface
    {
        return app(SmsServiceInterface::class);
    }
}
