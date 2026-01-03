<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AIRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('ai.rate_limiting.enabled', true)) {
            return $next($request);
        }

        $user = $request->user();
        $key = $user ? "ai_requests:user:{$user->id}" : "ai_requests:ip:{$request->ip()}";

        // Check per-minute limit
        $perMinute = config('ai.rate_limiting.max_requests_per_minute', 60);
        $executed = RateLimiter::attempt(
            "{$key}:minute",
            $perMinute,
            function() {
                return true;
            },
            60 // 1 minute
        );

        if (!$executed) {
            return response()->json([
                'message' => 'تم تجاوز الحد المسموح من الطلبات في الدقيقة',
                'retry_after' => RateLimiter::availableIn("{$key}:minute"),
            ], 429);
        }

        // Check per-hour limit
        $perHour = config('ai.rate_limiting.max_requests_per_hour', 1000);
        $executed = RateLimiter::attempt(
            "{$key}:hour",
            $perHour,
            function() {
                return true;
            },
            3600 // 1 hour
        );

        if (!$executed) {
            return response()->json([
                'message' => 'تم تجاوز الحد المسموح من الطلبات في الساعة',
                'retry_after' => RateLimiter::availableIn("{$key}:hour"),
            ], 429);
        }

        // Check per-day limit
        $perDay = config('ai.rate_limiting.max_requests_per_day', 10000);
        $executed = RateLimiter::attempt(
            "{$key}:day",
            $perDay,
            function() {
                return true;
            },
            86400 // 24 hours
        );

        if (!$executed) {
            return response()->json([
                'message' => 'تم تجاوز الحد المسموح من الطلبات في اليوم',
                'retry_after' => RateLimiter::availableIn("{$key}:day"),
            ], 429);
        }

        return $next($request);
    }
}


