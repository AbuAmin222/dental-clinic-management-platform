<?php

declare(strict_types=1);

namespace App\Exceptions\Sms;

/**
 * يُرمى عندما يتعذّر تطبيع رقم الهاتف المُعطى إلى صيغة دولية صالحة (E.164) قبل الإرسال.
 *
 * Thrown when the given phone number cannot be normalized into a valid international
 * (E.164) format before sending.
 */
class InvalidPhoneNumberException extends SmsException {}
