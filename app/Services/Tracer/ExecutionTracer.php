<?php

declare(strict_types=1);

namespace App\Services\Tracer;

use App\Contracts\Tracer\ExecutionTracerInterface;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;

class ExecutionTracer implements ExecutionTracerInterface
{
    protected ConsoleOutput $console;

    public function __construct()
    {
        $this->console = new ConsoleOutput();
    }

    public function mark(string $attribute, array $context = []): void
    {
        $timestamp = now()->format('Y-m-d H:i:s.v');
        $payload = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $formattedMessage = sprintf('[TRACE] [%s] REACHED: %s | Context: %s', $timestamp, $attribute, $payload);

        // 1. التدوين في ملفات السجل (Laravel Logs)
        Log::info($formattedMessage);

        // 2. الطباعة المباشرة على الـ Console
        if (app()->runningInConsole() || app()->environment('local')) {
            $this->console->writeln("<comment>{$formattedMessage}</comment>");
        }
    }
}
