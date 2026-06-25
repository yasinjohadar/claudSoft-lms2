<?php

namespace App\Services\CodeExecution;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PistonClient
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('challenges.piston.url'), '/');
        $this->timeout = (int) config('challenges.piston.timeout', 10);
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '';
    }

    public function ping(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }
        try {
            return Http::timeout(5)->get($this->baseUrl . '/api/v2/runtimes')->successful();
        } catch (\Throwable $e) {
            Log::warning('Piston ping failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function runtimes(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }
        return Cache::remember('piston.runtimes', 3600, function () {
            $response = Http::timeout(10)->get($this->baseUrl . '/api/v2/runtimes');
            return $response->successful() ? ($response->json() ?? []) : [];
        });
    }

    public function execute(string $language, array $files, string $stdin = '', ?string $version = null, ?int $runTimeoutMs = null): array
    {
        if (! $this->isConfigured()) {
            return $this->errorResult('خدمة تنفيذ الكود غير مُعدّة (PISTON_URL)');
        }
        $version = $version ?? config('challenges.piston.version', '*');
        $runTimeoutMs = $runTimeoutMs ?? ((int) config('challenges.piston.timeout', 10) * 1000);
        $started = microtime(true);
        try {
            $response = Http::timeout($this->timeout + 5)->post($this->baseUrl . '/api/v2/execute', [
                'language' => $language,
                'version' => $version,
                'files' => $files,
                'stdin' => $stdin,
                'args' => [],
                'compile_timeout' => min($runTimeoutMs, 10000),
                'run_timeout' => $runTimeoutMs,
            ]);
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            if (! $response->successful()) {
                return $this->errorResult('فشل الاتصال بمحرك التنفيذ: ' . $response->status(), $durationMs);
            }
            $body = $response->json();
            $run = $body['run'] ?? [];
            $compile = $body['compile'] ?? null;
            $stderr = trim((string) ($run['stderr'] ?? ''));
            if ($compile && ! empty($compile['stderr'])) {
                $stderr = trim($compile['stderr'] . "\n" . $stderr);
            }
            $exitCode = (int) ($run['code'] ?? ($compile['code'] ?? -1));
            return [
                'success' => $exitCode === 0 && $stderr === '',
                'message' => $exitCode === 0 ? 'تم التشغيل بنجاح' : 'انتهى التشغيل بخطأ',
                'stdout' => (string) ($run['stdout'] ?? $run['output'] ?? ''),
                'stderr' => $stderr,
                'exit_code' => $exitCode,
                'duration_ms' => $durationMs,
                'runtime_slug' => $language,
                'runtime_version' => $body['version'] ?? $version,
            ];
        } catch (\Throwable $e) {
            Log::error('Piston execute failed', ['error' => $e->getMessage()]);
            return $this->errorResult('تعذّر الاتصال بمحرك التنفيذ. تأكد من تشغيل Piston.', (int) round((microtime(true) - $started) * 1000));
        }
    }

    protected function errorResult(string $message, ?int $durationMs = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'stdout' => '',
            'stderr' => $message,
            'exit_code' => -1,
            'duration_ms' => $durationMs,
            'runtime_slug' => null,
            'runtime_version' => null,
        ];
    }
}