<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'jawwal_pay' => [
        'merchant_id' => env('JAWWAL_PAY_MERCHANT_ID'),
        'secret_key'  => env('JAWWAL_PAY_SECRET_KEY'),
        'base_url'    => env('JAWWAL_PAY_BASE_URL', 'https://checkout.jawwalpay.ps'),
    ],

    'bank_of_palestine' => [
        'merchant_id' => env('BOP_MERCHANT_ID'),
        'password'    => env('BOP_PASSWORD'),
        'base_url'    => env('BOP_BASE_URL', 'https://bop.gateway.mastercard.com'),
    ],
    'palpay' => [
        'merchant_id' => env('PALPAY_MERCHANT_ID', 'demo_merchant'),
        'base_url'    => env('PALPAY_BASE_URL', 'https://sandbox.palpay.ps'),
    ],

    'paypal' => [
        'client_id'   => env('PAYPAL_CLIENT_ID'),
        'secret_key'  => env('PAYPAL_SECRET'),
        'base_url'    => env('PAYPAL_BASE_URL', 'https://www.sandbox.paypal.com'),
    ],
    'exchange' => [
        'ils_to_usd' => env('ILS_TO_USD_RATE', 0.27),
    ],
    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'default_country_code' => env('SMS_DEFAULT_COUNTRY_CODE', '+970'),
        'log_channel' => env('SMS_LOG_CHANNEL', 'stack'),
    ],

];
