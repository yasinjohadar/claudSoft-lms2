<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    public static function resolveCauser(): ?User
    {
        $impersonatorId = session('impersonate.original_user_id');

        if ($impersonatorId) {
            return User::query()->find($impersonatorId);
        }

        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    public static function enrichActivity(Activity $activity): void
    {
        $causer = static::resolveCauser();
        if ($causer) {
            $activity->causer()->associate($causer);
        }

        $properties = $activity->properties?->toArray() ?? [];
        $properties['context'] = array_merge($properties['context'] ?? [], static::requestContext());

        $activity->properties = collect($properties);
    }

    /**
     * @return array<string, mixed>
     */
    public static function requestContext(): array
    {
        $request = Request::instance();

        return array_filter([
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'route' => $request->route()?->getName() ?? $request->path(),
            'method' => $request->method(),
            'request_id' => $request->header('X-Request-Id') ?? $request->attributes->get('request_id'),
            'impersonator_id' => session('impersonate.original_user_id'),
            'acting_user_id' => auth()->id(),
        ], fn ($value) => $value !== null && $value !== '');
    }

    public static function log(
        string $logName,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?User $causer = null,
    ): void {
        try {
            $builder = activity($logName)
                ->causedBy($causer ?? static::resolveCauser())
                ->withProperties(array_merge(['context' => static::requestContext()], $properties));

            if ($subject) {
                $builder->performedOn($subject);
            }

            $builder->log($description);
        } catch (\Throwable $e) {
            Log::channel('audit')->error('Failed to store activity log', [
                'log_name' => $logName,
                'description' => $description,
                'subject' => $subject ? $subject::class.':'.$subject->getKey() : null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function logRoleSync(User $user, array $oldRoles, array $newRoles, ?User $causer = null): void
    {
        static::log(
            logName: 'users',
            description: 'تحديث أدوار المستخدم',
            subject: $user,
            properties: [
                'event' => 'roles_synced',
                'old' => ['roles' => $oldRoles],
                'attributes' => ['roles' => $newRoles],
            ],
            causer: $causer,
        );
    }

    public static function logImpersonationStarted(User $admin, User $target): void
    {
        static::log(
            logName: 'security',
            description: 'بدء الدخول كمستخدم آخر',
            subject: $target,
            properties: [
                'event' => 'impersonation_started',
                'target_user_id' => $target->id,
                'target_user_name' => $target->name,
            ],
            causer: $admin,
        );
    }

    public static function logImpersonationStopped(User $admin, ?User $target = null): void
    {
        static::log(
            logName: 'security',
            description: 'إنهاء الدخول كمستخدم آخر',
            subject: $target ?? $admin,
            properties: [
                'event' => 'impersonation_stopped',
                'target_user_id' => $target?->id,
            ],
            causer: $admin,
        );
    }

    public static function logBulkImport(User $admin, string $filename, int $created, int $updated, int $failed): void
    {
        static::log(
            logName: 'users',
            description: 'استيراد جماعي للمستخدمين',
            properties: [
                'event' => 'bulk_import',
                'filename' => $filename,
                'created' => $created,
                'updated' => $updated,
                'failed' => $failed,
            ],
            causer: $admin,
        );
    }

    public static function logLogin(User $user, bool $successful, ?string $failureReason = null): void
    {
        static::log(
            logName: 'security',
            description: $successful ? 'تسجيل دخول ناجح' : 'محاولة تسجيل دخول فاشلة',
            subject: $user,
            properties: array_filter([
                'event' => $successful ? 'login_success' : 'login_failed',
                'failure_reason' => $failureReason,
            ]),
            causer: $user,
        );
    }

    public static function logLogout(User $user): void
    {
        static::log(
            logName: 'security',
            description: 'تسجيل خروج',
            subject: $user,
            properties: ['event' => 'logout'],
            causer: $user,
        );
    }
}
