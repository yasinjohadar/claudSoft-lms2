<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\DeviceTrackingService;
use Symfony\Component\HttpFoundation\Response;

class CheckDeviceBlocked
{
    protected DeviceTrackingService $deviceTrackingService;

    public function __construct(DeviceTrackingService $deviceTrackingService)
    {
        $this->deviceTrackingService = $deviceTrackingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (Auth::check()) {
            try {
                $user = Auth::user();

                if ($this->deviceTrackingService->isDeviceBlocked($user, $request)) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')
                        ->withErrors([
                            'email' => 'تم حظر هذا الجهاز. يرجى التواصل مع الإدارة.',
                        ]);
                }
            } catch (\Throwable $e) {
                // Never block the whole app if device lookup fails.
                report($e);
            }
        }

        return $next($request);
    }
}
