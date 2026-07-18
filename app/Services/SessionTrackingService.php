<?php

namespace App\Services;

use App\Models\SessionActivity;
use App\Models\User;
use App\Models\UserSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SessionTrackingService
{
    protected DeviceTrackingService $deviceTrackingService;

    protected GeoLocationService $geoLocationService;

    protected bool $lastActivitySkipped = false;

    public function __construct(
        DeviceTrackingService $deviceTrackingService,
        GeoLocationService $geoLocationService
    ) {
        $this->deviceTrackingService = $deviceTrackingService;
        $this->geoLocationService = $geoLocationService;
    }

    public function wasLastActivitySkipped(): bool
    {
        return $this->lastActivitySkipped;
    }

    /**
     * @return list<string>
     */
    public function clientAllowedTypes(): array
    {
        return array_values(array_unique(array_merge(
            config('session_tracking.skip_if_recent', []),
            config('session_tracking.update_if_recent', []),
            config('session_tracking.always_insert', []),
        )));
    }

    /**
     * Start a new session for a user.
     */
    public function startSession(User $user, Request $request): ?UserSession
    {
        try {
            $device = $this->deviceTrackingService->trackDeviceOnLogin($user, $request);

            $deviceInfo = $this->deviceTrackingService->detectDeviceInfo($request);

            $ipAddress = $request->ip();
            $location = $this->geoLocationService->getLocationFromIp($ipAddress);

            $meta = [
                'device_id' => $device->id,
                'device_fingerprint' => $device->device_fingerprint,
            ];

            if ($location) {
                $meta['location'] = $location;
            }

            $session = UserSession::create([
                'user_id' => $user->id,
                'session_uuid' => Str::uuid(),
                'session_name' => 'جلسة '.now()->format('Y-m-d H:i'),
                'started_at' => now(),
                'status' => 'active',
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'browser_version' => $deviceInfo['browser_version'],
                'platform' => $deviceInfo['platform'],
                'platform_version' => $deviceInfo['platform_version'],
                'screen_resolution' => $this->getScreenResolution($request),
                'connection_type' => $this->detectConnectionType($request),
                'meta' => $meta,
            ]);

            $this->trackActivity($session->id, 'session_start', [
                'page_url' => $request->fullUrl(),
                'referrer' => $request->header('referer'),
            ]);

            $request->session()->put('user_session_id', $session->id);

            return $session;
        } catch (\Exception $e) {
            Log::error('Failed to start session tracking', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ]);

            return null;
        }
    }

    /**
     * End a session.
     */
    public function endSession(?int $sessionId = null, ?Request $request = null): bool
    {
        try {
            if (! $sessionId && $request) {
                $sessionId = $request->session()->get('user_session_id');
            }

            if (! $sessionId) {
                return false;
            }

            $session = UserSession::find($sessionId);

            if (! $session || $session->status !== 'active') {
                return false;
            }

            $endedAt = now();
            $duration = $endedAt->diffInSeconds($session->started_at);

            $session->update([
                'ended_at' => $endedAt,
                'duration_seconds' => $duration,
                'status' => 'completed',
            ]);

            $this->trackActivity($session->id, 'session_end', [
                'duration_seconds' => $duration,
                'page_url' => $request ? $request->fullUrl() : null,
            ]);

            if ($request) {
                $request->session()->forget('user_session_id');
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to end session tracking', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Track an activity with smart dedup / update / always-insert policies.
     */
    public function trackActivity(int $sessionId, string $activityType, array $data = []): ?SessionActivity
    {
        $this->lastActivitySkipped = false;

        if (! config('session_tracking.enabled', true)) {
            return null;
        }

        try {
            $session = UserSession::find($sessionId);

            if (! $session) {
                return null;
            }

            $hash = $this->detailsHash($data);

            if ($this->isAlwaysInsert($activityType) || $this->isServerOrMiddlewareOwned($activityType)) {
                return $this->insertActivity($sessionId, $activityType, $data, $hash);
            }

            if ($this->isSkipIfRecent($activityType)) {
                return $this->handleSkipIfRecent($sessionId, $activityType, $data, $hash);
            }

            if ($this->isUpdateIfRecent($activityType)) {
                return $this->handleUpdateIfRecent($sessionId, $activityType, $data, $hash);
            }

            return $this->insertActivity($sessionId, $activityType, $data, $hash);
        } catch (\Exception $e) {
            Log::error('Failed to track activity', [
                'session_id' => $sessionId,
                'activity_type' => $activityType,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ]);

            return null;
        }
    }

    public function trackPageView(int $sessionId, string $pageUrl, array $additionalData = []): ?SessionActivity
    {
        return $this->trackActivity($sessionId, 'page_view', array_merge([
            'page_url' => $pageUrl,
        ], $additionalData));
    }

    public function trackAction(int $sessionId, string $actionName, array $data = []): ?SessionActivity
    {
        return $this->trackActivity($sessionId, 'action', array_merge([
            'action_name' => $actionName,
        ], $data));
    }

    public function trackFocusChange(int $sessionId, bool $hasFocus): ?SessionActivity
    {
        $activityType = $hasFocus ? 'focus_gained' : 'focus_lost';

        return $this->trackActivity($sessionId, $activityType, [
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function trackIdle(int $sessionId, bool $isIdle): ?SessionActivity
    {
        $activityType = $isIdle ? 'idle_start' : 'idle_end';

        return $this->trackActivity($sessionId, $activityType, [
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function updateSessionActivity(int $sessionId): bool
    {
        try {
            $session = UserSession::find($sessionId);

            if (! $session || $session->status !== 'active') {
                return false;
            }

            $session->touch();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update session activity', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getCurrentSession(User $user): ?UserSession
    {
        return UserSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest('started_at')
            ->first();
    }

    protected function handleSkipIfRecent(int $sessionId, string $activityType, array $data, string $hash): ?SessionActivity
    {
        if ($this->isRecentInCache($sessionId, $activityType)) {
            $this->lastActivitySkipped = true;

            return $this->latestActivity($sessionId, $activityType);
        }

        $latest = $this->latestActivity($sessionId, $activityType);
        if ($latest && $this->isWithinDedupWindow($latest->occurred_at)) {
            $this->rememberCache($sessionId, $activityType, $hash);
            $this->lastActivitySkipped = true;

            return $latest;
        }

        return $this->insertActivity($sessionId, $activityType, $data, $hash);
    }

    protected function handleUpdateIfRecent(int $sessionId, string $activityType, array $data, string $hash): ?SessionActivity
    {
        $latest = null;

        if ($this->isRecentInCache($sessionId, $activityType)) {
            $latest = $this->latestActivity($sessionId, $activityType);
        } else {
            $latest = $this->latestActivity($sessionId, $activityType);
            if ($latest && $this->isWithinDedupWindow($latest->occurred_at)) {
                $this->rememberCache($sessionId, $activityType, $hash);
            } else {
                $latest = null;
            }
        }

        if ($latest && $this->isWithinDedupWindow($latest->occurred_at)) {
            $latest->update([
                'activity_details' => array_merge((array) $latest->activity_details, $data),
                'page_url' => $data['page_url'] ?? $latest->page_url,
                'occurred_at' => now(),
            ]);

            $this->rememberCache($sessionId, $activityType, $hash);

            return $latest->fresh();
        }

        return $this->insertActivity($sessionId, $activityType, $data, $hash);
    }

    protected function insertActivity(int $sessionId, string $activityType, array $data, string $hash): SessionActivity
    {
        $activity = SessionActivity::create([
            'user_session_id' => $sessionId,
            'activity_type' => $activityType,
            'activity_details' => $data,
            'page_url' => $data['page_url'] ?? null,
            'occurred_at' => now(),
        ]);

        $this->rememberCache($sessionId, $activityType, $hash);

        return $activity;
    }

    protected function latestActivity(int $sessionId, string $activityType): ?SessionActivity
    {
        return SessionActivity::query()
            ->where('user_session_id', $sessionId)
            ->where('activity_type', $activityType)
            ->latest('occurred_at')
            ->first();
    }

    protected function isRecentInCache(int $sessionId, string $activityType): bool
    {
        $payload = Cache::get($this->cacheKey($sessionId, $activityType));
        if (! is_array($payload) || empty($payload['occurred_at'])) {
            return false;
        }

        try {
            $occurredAt = Carbon::parse($payload['occurred_at']);
        } catch (\Throwable) {
            return false;
        }

        return $this->isWithinDedupWindow($occurredAt);
    }

    protected function isWithinDedupWindow(mixed $occurredAt): bool
    {
        if (! $occurredAt) {
            return false;
        }

        $at = $occurredAt instanceof Carbon ? $occurredAt : Carbon::parse($occurredAt);
        $seconds = max(1, (int) config('session_tracking.dedup_seconds', 30));

        return $at->greaterThanOrEqualTo(now()->subSeconds($seconds));
    }

    protected function rememberCache(int $sessionId, string $activityType, string $hash = ''): void
    {
        Cache::put($this->cacheKey($sessionId, $activityType), [
            'occurred_at' => now()->toIso8601String(),
            'last_type' => $activityType,
            'last_hash' => $hash,
        ], max(1, (int) config('session_tracking.cache_ttl_seconds', 35)));
    }

    protected function cacheKey(int $sessionId, string $activityType): string
    {
        return "session_activity:last:{$sessionId}:{$activityType}";
    }

    protected function detailsHash(array $data): string
    {
        return sha1((string) json_encode(Arr::sortRecursive($data)));
    }

    protected function isAlwaysInsert(string $type): bool
    {
        return in_array($type, config('session_tracking.always_insert', []), true);
    }

    protected function isSkipIfRecent(string $type): bool
    {
        return in_array($type, config('session_tracking.skip_if_recent', []), true);
    }

    protected function isUpdateIfRecent(string $type): bool
    {
        return in_array($type, config('session_tracking.update_if_recent', []), true);
    }

    protected function isServerOrMiddlewareOwned(string $type): bool
    {
        return in_array($type, config('session_tracking.server_only', []), true)
            || in_array($type, config('session_tracking.middleware_only', []), true);
    }

    protected function getScreenResolution(Request $request): ?string
    {
        $width = $request->header('X-Screen-Width');
        $height = $request->header('X-Screen-Height');

        if ($width && $height) {
            return "{$width}x{$height}";
        }

        return null;
    }

    protected function detectConnectionType(Request $request): ?string
    {
        $connectionType = $request->header('X-Connection-Type');

        if ($connectionType && in_array($connectionType, ['wifi', 'cellular', 'ethernet'], true)) {
            return $connectionType;
        }

        return 'unknown';
    }
}
