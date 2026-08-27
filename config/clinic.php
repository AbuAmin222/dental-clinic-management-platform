<?php

declare(strict_types=1);

return [

    'money' => [
        'minor_unit_factor' => (int) env('CLINIC_MONEY_MINOR_UNIT_FACTOR', 100),
    ],

    'appointments' => [
        'default_duration_minutes' => (int) env('CLINIC_APPOINTMENT_DEFAULT_DURATION_MINUTES', 30),
    ],

    'risk' => [
        'amount_threshold_minor_units' => (int) env('CLINIC_RISK_AMOUNT_THRESHOLD_MINOR_UNITS', 500_000), // 5,000.00
        'amount_threshold_points'      => (int) env('CLINIC_RISK_AMOUNT_THRESHOLD_POINTS', 40),

        'velocity_lookback_minutes'  => (int) env('CLINIC_RISK_VELOCITY_LOOKBACK_MINUTES', 15),
        'velocity_attempt_threshold' => (int) env('CLINIC_RISK_VELOCITY_ATTEMPT_THRESHOLD', 4),
        'velocity_points'            => (int) env('CLINIC_RISK_VELOCITY_POINTS', 35),

        'hold_threshold' => (int) env('CLINIC_RISK_HOLD_THRESHOLD', 70),
    ],

    'uploads' => [
        'xray' => [
            'mimes'  => ['jpeg', 'png', 'jpg', 'webp'],
            'max_kb' => (int) env('CLINIC_UPLOAD_XRAY_MAX_KB', 5120),
        ],
        'identity_document' => [
            'mimes'  => ['jpg', 'jpeg', 'png'],
            'max_kb' => (int) env('CLINIC_UPLOAD_IDENTITY_MAX_KB', 4096),
        ],
    ],

    'validation' => [
        'phone_regex' => env('CLINIC_PHONE_REGEX', '/^(059|056)\d{7}$/'),
    ],

    'dental_chart' => [
        'tooth_number_min' => (int) env('CLINIC_TOOTH_NUMBER_MIN', 1),
        'tooth_number_max' => (int) env('CLINIC_TOOTH_NUMBER_MAX', 32),
    ],

    'account_security' => [
        // طول رمز التحقق الرقمي المُرسَل للمريض عند أول دخول، ومدة صلاحيته بالدقائق.
        'verification_code_length'          => (int) env('CLINIC_VERIFICATION_CODE_LENGTH', 6),
        'verification_code_expiry_minutes'  => (int) env('CLINIC_VERIFICATION_CODE_EXPIRY_MINUTES', 15),
        // الحد الأقصى لمحاولات إعادة إرسال الرمز خلال ساعة واحدة (حماية من إساءة الاستخدام
        // ومن استنزاف حصة مزوّد البريد/الرسائل).
        'verification_code_resend_max_per_hour' => (int) env('CLINIC_VERIFICATION_CODE_RESEND_MAX_PER_HOUR', 5),
    ],

    'pagination' => [
        'default'   => (int) env('CLINIC_PAGINATION_DEFAULT', 15),
        'financial' => (int) env('CLINIC_PAGINATION_FINANCIAL', 20),
    ],
    'user_count' => [
        'CLINIC_USER_COUNT'              =>  (int) env('CLINIC_USER_COUNT', 1),
        'CLINIC_ADMIN_USER_COUNT'        =>  (int) env('CLINIC_ADMIN_USER_COUNT', 1),
        'CLINIC_DOCTOR_USER_COUNT'       =>  (int) env('CLINIC_DOCTOR_USER_COUNT', 1),
        'CLINIC_FINANTIAL_USER_COUNT'    =>  (int) env('CLINIC_FINANTIAL_USER_COUNT', 1),
        'CLINIC_RECEPTIONIST_USER_COUNT' =>  (int) env('CLINIC_RECEPTIONIST_USER_COUNT', 1),
        'CLINIC_PATIENT_USER_COUNT'      =>  (int) env('CLINIC_PATIENT_USER_COUNT', 1),
    ],
    'service_count' => [
        'CLINIC_APPINTMENT_COUNT'              =>  (int) env('CLINIC_APPINTMENT_COUNT', 1),
        'CLINIC_INVOICE_COUNT'        =>  (int) env('CLINIC_INVOICE_COUNT', 1),
        'CLINIC_PRICING_COUNT'        =>  (int) env('CLINIC_PRICING_COUNT', 1),
    ]

];
