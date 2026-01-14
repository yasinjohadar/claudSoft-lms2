<?php

namespace App\Http\Controllers;

use App\Services\SessionTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $request->validate([
                'activity_type' => 'required|string|in:page_view,action,focus_lost,focus_gained,idle_start,idle_end,disconnect,reconnect',
                'page_url' => 'nullable|string|max:2048',
                'activity_details' => 'nullable|array',
            ]);

            $sessionId = $request->session()->get('user_session_id');
            
            if (!$sessionId) {
                // Try to get current session
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

            // Add page URL to details if provided
            if ($pageUrl) {
                $activityDetails['page_url'] = $pageUrl;
            }

            $activity = $this->sessionTrackingService->trackActivity(
                $sessionId,
                $activityType,
                $activityDetails
            );

            if ($activity) {
                return response()->json(['success' => true, 'activity_id' => $activity->id]);
            }

            return response()->json(['success' => false, 'message' => 'Failed to track activity'], 500);
        } catch (\Exception $e) {
            Log::error('Failed to track activity from frontend', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while tracking activity'
            ], 500);
        }
    }

    /**
     * Update session activity (heartbeat).
     */
    public function heartbeat(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['success' => false], 401);
            }

            $sessionId = $request->session()->get('user_session_id');
            
            if (!$sessionId) {
                $currentSession = $this->sessionTrackingService->getCurrentSession(Auth::user());
                if ($currentSession) {
                    $sessionId = $currentSession->id;
                    $request->session()->put('user_session_id', $sessionId);
                } else {
                    return response()->json(['success' => false], 400);
                }
            }

            $this->sessionTrackingService->updateSessionActivity($sessionId);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}
