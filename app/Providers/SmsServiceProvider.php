<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Sms\SmsGatewayInterface;
use App\Contracts\Sms\SmsServiceInterface;
use App\Notifications\Channels\SmsChannel;
use App\Services\Sms\Drivers\LogSmsGateway;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register SMS domain services.
     */
    public function register(): void
    {
        $this->app->bind(SmsGatewayInterface::class, function (): SmsGatewayInterface {
            $driver = (string) config('services.sms.driver', 'log');

            return match ($driver) {
                'log' => new LogSmsGateway((string) config('services.sms.log_channel', 'stack')),
                // 'twilio' => new TwilioSmsGateway(...),      // مزوّد حقيقي — يُضاف لاحقاً بعد اختيار الحساب
                // 'local-gateway' => new LocalSmsGateway(...), // مزوّد فلسطيني محلي — يُضاف لاحقاً
                default => throw new InvalidArgumentException(
                    "Unsupported SMS driver [{$driver}]. Configure SMS_DRIVER in .env to a supported value (currently only 'log' is implemented)."
                ),
            };
        });

        $this->app->singleton(SmsServiceInterface::class, function ($app): SmsServiceInterface {
            return new SmsService(
                gateway: $app->make(SmsGatewayInterface::class),
                defaultCountryCode: (string) config('services.sms.default_country_code', '+970'),
            );
        });
    }

    /**
     * Bootstrap SMS domain services.
     */
    public function boot(): void
    {
        Notification::extend('sms', function ($app): SmsChannel {
            return new SmsChannel($app->make(SmsServiceInterface::class));
        });
    }
}
