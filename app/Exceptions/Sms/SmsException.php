<?php

declare(strict_types=1);

namespace App\Exceptions\Sms;

use RuntimeException;

/**
 * الاستثناء الأساسي (الجذر) لكل أخطاء نطاق SMS — مطابق تماماً لدور
 * {@see \App\Exceptions\Storage\StorageException} في نطاق التخزين. الإمساك (catch) بهذا
 * الكلاس وحده يكفي لاعتراض أي خطأ متعلق بـ SMS بغضّ النظر عن نوعه الدقيق.
 *
 * Base (root) exception for every SMS-domain error — mirrors the exact role of
 * {@see \App\Exceptions\Storage\StorageException} in the storage domain. Catching this
 * class alone is enough to intercept any SMS-related error regardless of its specific type.
 */
class SmsException extends RuntimeException {}
