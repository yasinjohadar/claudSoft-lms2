<?php

namespace App\Http\Controllers;

use App\Services\SessionTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SessionActivityController extends Controller
{
    protected SessionTrackingService $sessionTrackingService;

    public function __construct(SessionTrackingService $sessionTrackingService)
    {
        $this->sessionTrackingService = $sessionTrackingService;
    }

    /**
     * Track activity from frontend JavaScript.
     */
    public function track(Request $request)
    {
        try {
            if (! Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $allowed = $this->sessionTrackingService->clientAllowedTypes();

            $request->validate([
                'activity_type' => ['required', 'string', Rule::in($allowed)],
                'page_url' => 'nullable|string|max:2048',
                'activity_details' => 'nullable|array',
            ]);

            $sessionId = $request->session()->get('user_session_id');

            if (! $sessionId) {
                $currentSession = $this->sessionTrackingService->getCurrentSession(Auth::user());
                if ($currentSession) {
                    $sessionId = $currentSession->id;
                    $request->session()->put('user_session_id', $sessionId);
                } else {
                    return response()->json(['success' => false, 'message' => 'No active session'], 400);
                }
            }

            $activityType = $request->input('activity_type');
            $pageUrl = $request->input('page_url');
            $activityDetails = $request->input('activity_details', []);

            if ($pageUrl) {
                $activityDetails['page_url'] = $pageUrl;
            }

            $activity = $this->sessionTrackingService->trackActivity(
                (int) $sessionId,
                $activityType,
                $activityDetails
            );

            if ($this->sessionTrackingService->wasLastActivitySkipped()) {
                return response()->json([
                    'success' => true,
                    'skipped' => true,
                    'activity_id' => $activity?->id,
                ]);
            }

            if ($activity) {
                return response()->json([
                    'success' => true,
                    'skipped' => false,
                    'activity_id' => $activity->id,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Failed to track activity'], 500);
        } catch (ValidationException $e) {
            $this->maybeWarnUnknownActivityType($request->input('activity_type'));

            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to track activity from frontend', [
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while tracking activity',
            ], 500);
        }
    }

    /**
     * Update session activity (heartbeat).
     */
    public function heartbeat(Request $request)
    {
        try {
            if (! Auth::check()) {
                return response()->json(['success' => false], 401);
            }

            $sessionId = $request->session()->get('user_session_id');

            if (! $sessionId) {
                $currentSession = $this->sessionTrackingService->getCurrentSession(Auth::user());
                if ($currentSession) {
                    $sessionId = $currentSession->id;
                    $request->session()->put('user_session_id', $sessionId);
                } else {
                    return response()->json(['success' => false], 400);
                }
            }

            $this->sessionTrackingService->updateSessionActivity((int) $sessionId);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Failed to update session heartbeat', [
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    protected function maybeWarnUnknownActivityType(mixed $activityType): void
    {
        if (! is_string($activityType) || $activityType === '') {
            return;
        }

        $allowed = $this->sessionTrackingService->clientAllowedTypes();
        if (in_array($activityType, $allowed, true)) {
            return;
        }

        $window = max(1, (int) config('session_tracking.unknown_type_warning_window_seconds', 60));
        $threshold = max(1, (int) config('session_tracking.unknown_type_warning_threshold', 20));
        $counterKey = 'session_activity:unknown_count:'.$activityType;
        $warnedKey = 'session_activity:unknown_warned:'.$activityType;

        $count = (int) Cache::increment($counterKey);
        if ($count === 1) {
            Cache::put($counterKey, 1, $window);
        }

        if ($count < $threshold || Cache::has($warnedKey)) {
            return;
        }

        Cache::put($warnedKey, true, $window);

        Log::warning('Repeated unknown session activity_type from client', [
            'activity_type' => $activityType,
            'count' => $count,
            'window_seconds' => $window,
        ]);
    }
}
