<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use App\Models\SessionActivity;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SessionTrackingService
{
    protected DeviceTrackingService $deviceTrackingService;
    protected GeoLocationService $geoLocationService;

    public function __construct(
        DeviceTrackingService $deviceTrackingService,
        GeoLocationService $geoLocationService
    ) {
        $this->deviceTrackingService = $deviceTrackingService;
        $this->geoLocationService = $geoLocationService;
    }

    /**
     * Start a new session for a user.
     */
    public function startSession(User $user, Request $request): ?UserSession
    {
        try {
            // First, track the device
            $device = $this->deviceTrackingService->trackDeviceOnLogin($user, $request);
            
            // Get device info
            $deviceInfo = $this->deviceTrackingService->detectDeviceInfo($request);
            
            // Get geolocation
            $ipAddress = $request->ip();
            $location = $this->geoLocationService->getLocationFromIp($ipAddress);
            
            // Prepare meta data
            $meta = [
                'device_id' => $device->id,
                'device_fingerprint' => $device->device_fingerprint,
            ];
            
            // Add location to meta if available
            if ($location) {
                $meta['location'] = $location;
            }
            
            // Create new session
            $session = UserSession::create([
                'user_id' => $user->id,
                'session_uuid' => Str::uuid(),
                'session_name' => 'جلسة ' . now()->format('Y-m-d H:i'),
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

            // Track session_start activity
            $this->trackActivity($session->id, 'session_start', [
                'page_url' => $request->fullUrl(),
                'referrer' => $request->header('referer'),
            ]);

            // Store session ID in session for later use
            $request->session()->put('user_session_id', $session->id);

            return $session;
        } catch (\Exception $e) {
            Log::error('Failed to start session tracking', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * End a session.
     */
    public function endSession(?int $sessionId = null, Request $request = null): bool
    {
        try {
            // Get session ID from request if not provided
            if (!$sessionId && $request) {
                $sessionId = $request->session()->get('user_session_id');
            }

            if (!$sessionId) {
                return false;
            }

            $session = UserSession::find($sessionId);
            
            if (!$session || $session->status !== 'active') {
                return false;
            }

            $endedAt = now();
            $duration = $endedAt->diffInSeconds($session->started_at);

            $session->update([
                'ended_at' => $endedAt,
                'duration_seconds' => $duration,
                'status' => 'completed',
            ]);

            // Track session_end activity
            $this->trackActivity($session->id, 'session_end', [
                'duration_seconds' => $duration,
                'page_url' => $request ? $request->fullUrl() : null,
            ]);

            // Clear session ID from session
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
     * Track an activity.
     */
    public function trackActivity(int $sessionId, string $activityType, array $data = []): ?SessionActivity
    {
        try {
            $session = UserSession::find($sessionId);
            
            if (!$session) {
                return null;
            }

            $activity = SessionActivity::create([
                'user_session_id' => $sessionId,
                'activity_type' => $activityType,
                'activity_details' => $data,
                'page_url' => $data['page_url'] ?? null,
                'occurred_at' => now(),
            ]);

            return $activity;
        } catch (\Exception $e) {
            Log::error('Failed to track activity', [
                'session_id' => $sessionId,
                'activity_type' => $activityType,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Track a page view.
     */
    public function trackPageView(int $sessionId, string $pageUrl, array $additionalData = []): ?SessionActivity
    {
        return $this->trackActivity($sessionId, 'page_view', array_merge([
            'page_url' => $pageUrl,
        ], $additionalData));
    }

    /**
     * Track an action.
     */
    public function trackAction(int $sessionId, string $actionName, array $data = []): ?SessionActivity
    {
        return $this->trackActivity($sessionId, 'action', array_merge([
            'action_name' => $actionName,
        ], $data));
    }

    /**
     * Track focus change.
     */
    public function trackFocusChange(int $sessionId, bool $hasFocus): ?SessionActivity
    {
        $activityType = $hasFocus ? 'focus_gained' : 'focus_lost';
        return $this->trackActivity($sessionId, $activityType, [
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Track idle state.
     */
    public function trackIdle(int $sessionId, bool $isIdle): ?SessionActivity
    {
        $activityType = $isIdle ? 'idle_start' : 'idle_end';
        return $this->trackActivity($sessionId, $activityType, [
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Update session activity (heartbeat).
     */
    public function updateSessionActivity(int $sessionId): bool
    {
        try {
            $session = UserSession::find($sessionId);
            
            if (!$session || $session->status !== 'active') {
                return false;
            }

            // Just update the session timestamp to indicate activity
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

    /**
     * Get current active session for user.
     */
    public function getCurrentSession(User $user): ?UserSession
    {
        return UserSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest('started_at')
            ->first();
    }

    /**
     * Get screen resolution from request (if available via JavaScript).
     */
    protected function getScreenResolution(Request $request): ?string
    {
        $width = $request->header('X-Screen-Width');
        $height = $request->header('X-Screen-Height');
        
        if ($width && $height) {
            return "{$width}x{$height}";
        }
        
        return null;
    }

    /**
     * Detect connection type from request.
     */
    protected function detectConnectionType(Request $request): ?string
    {
        // This is a simplified detection
        // In a real scenario, you might use JavaScript to detect this
        $connectionType = $request->header('X-Connection-Type');
        
        if ($connectionType && in_array($connectionType, ['wifi', 'cellular', 'ethernet'])) {
            return $connectionType;
        }
        
        return 'unknown';
    }
}
