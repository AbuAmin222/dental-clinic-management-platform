<?php

declare(strict_types=1);

namespace App\Exceptions\Sms;

/**
 * يُرمى عند فشل مزوّد SMS الفعلي في تسليم/قبول الرسالة (خطأ شبكة، رفض بيانات الاعتماد،
 * رقم مرفوض من طرف المزوّد...).
 *
 * Thrown when the actual SMS provider fails to deliver/accept the message (network
 * error, rejected credentials, number rejected by the provider...).
 */
class SmsDeliveryException extends SmsException {}
