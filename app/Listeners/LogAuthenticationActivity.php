<?php

namespace App\Listeners;

use App\Models\LoginLog;
use App\Models\User;
use App\Services\Admin\ActivityLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class LogAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        ActivityLogService::logLogin($event->user, true);
        $this->storeLoginLog($event->user, true);
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        ActivityLogService::logLogout($event->user);
        $this->closeLoginLog($event->user);
    }

    public function handleFailed(Failed $event): void
    {
        $user = User::query()->where('email', $event->credentials['email'] ?? '')->first();

        ActivityLogService::log(
            logName: 'security',
            description: 'محاولة تسجيل دخول فاشلة',
            subject: $user,
            properties: [
                'event' => 'login_failed',
                'email' => $event->credentials['email'] ?? null,
                'failure_reason' => 'invalid_credentials',
            ],
            causer: $user,
        );

        $this->storeLoginLog($user, false, 'invalid_credentials');
    }

    protected function storeLoginLog(?User $user, bool $successful, ?string $failureReason = null): void
    {
        try {
            LoginLog::create([
                'user_id' => $user?->id,
                'ip_address' => Request::ip() ?? '0.0.0.0',
                'user_agent' => Request::userAgent() ?? '',
                'is_successful' => $successful,
                'failure_reason' => $failureReason,
                'login_at' => now(),
                'session_id' => session()->getId(),
            ]);
        } catch (\Throwable) {
            // non-blocking
        }
    }

    protected function closeLoginLog(User $user): void
    {
        try {
            $log = LoginLog::query()
                ->where('user_id', $user->id)
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first();

            if ($log) {
                $logoutAt = now();
                $log->update([
                    'logout_at' => $logoutAt,
                    'session_duration_seconds' => $log->login_at
                        ? $log->login_at->diffInSeconds($logoutAt)
                        : null,
                ]);
            }
        } catch (\Throwable) {
            // non-blocking
        }
    }

    public function subscribe($events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
        ];
    }
}
