<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SessionTrackingService;
use Symfony\Component\HttpFoundation\Response;

class SessionTrackingMiddleware
{
    protected SessionTrackingService $sessionTrackingService;

    public function __construct(SessionTrackingService $sessionTrackingService)
    {
        $this->sessionTrackingService = $sessionTrackingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only track for authenticated users
        if (Auth::check()) {
            try {
                $user = Auth::user();

                // Get current session ID from session
                $sessionId = $request->session()->get('user_session_id');

                // If no session ID, try to get the latest active session
                if (! $sessionId) {
                    $currentSession = $this->sessionTrackingService->getCurrentSession($user);
                    if ($currentSession) {
                        $sessionId = $currentSession->id;
                        $request->session()->put('user_session_id', $sessionId);
                    }
                }

                // Track page view if session exists
                if ($sessionId && $this->shouldTrackPageView($request)) {
                    $this->sessionTrackingService->trackPageView(
                        $sessionId,
                        $request->fullUrl(),
                        [
                            'method' => $request->method(),
                            'referrer' => $request->header('referer'),
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // Never interrupt the request if analytics tracking fails.
                report($e);
            }
        }

        $response = $next($request);

        return $response;
    }

    /**
     * Determine if we should track this page view.
     */
    protected function shouldTrackPageView(Request $request): bool
    {
        // Don't track AJAX requests (they're handled by JavaScript)
        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        // Don't track asset requests
        $path = $request->path();
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return false;
        }

        // Don't track API routes
        if ($request->is('api/*')) {
            return false;
        }

        return true;
    }
}
