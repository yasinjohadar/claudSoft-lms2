<?php

namespace App\Http\Middleware;

use App\Services\SessionDeviceBindingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionDeviceBinding
{
    public function __construct(
        protected SessionDeviceBindingService $bindingService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        // Allow logout even when the bound device no longer matches.
        if ($request->routeIs('logout') || $request->is('logout')) {
            return $next($request);
        }

        $user = Auth::user();

        if ($this->bindingService->validate($user, $request)) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم إنهاء الجلسة لأن الجهاز الحالي لا يطابق الجهاز المرتبط بتسجيل الدخول.',
                'code' => 'session_device_mismatch',
            ], 401);
        }

        return redirect()
            ->route('login')
            ->with('error', 'تم إنهاء الجلسة لأن الجهاز الحالي لا يطابق الجهاز المرتبط بتسجيل الدخول. قد تكون الجلسة نُقلت من جهاز آخر.');
    }
}
