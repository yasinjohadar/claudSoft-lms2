<?php

namespace App\Http\Middleware;

use App\Services\DeviceSecuritySettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSingleSession
{
    public function __construct(
        protected DeviceSecuritySettingsService $settingsService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if (! $this->settingsService->isSingleSessionActiveForUser($user)) {
            return $next($request);
        }

        // Always re-read from DB: the in-session user may have a stale active_session_id.
        $activeSessionId = \App\Models\User::query()
            ->whereKey($user->id)
            ->value('active_session_id');
        $currentSessionId = $request->session()->getId();

        // First request after enabling the feature or legacy sessions without a stamp:
        // claim the current session instead of immediately logging the user out.
        if ($activeSessionId === null || $activeSessionId === '') {
            \App\Models\User::query()
                ->whereKey($user->id)
                ->update(['active_session_id' => $currentSessionId]);

            return $next($request);
        }

        if (hash_equals((string) $activeSessionId, (string) $currentSessionId)) {
            return $next($request);
        }

        // After login, some session drivers hand the next request a new id while
        // preserving auth. Re-claim once when this request still carries our stamp.
        $loginStamp = $request->session()->get('_single_session_stamp');
        if (is_string($loginStamp) && hash_equals((string) $activeSessionId, $loginStamp)) {
            \App\Models\User::query()
                ->whereKey($user->id)
                ->update(['active_session_id' => $currentSessionId]);
            $request->session()->put('_single_session_stamp', $currentSessionId);

            return $next($request);
        }

        // Allow explicit logout even when this browser is the displaced session —
        // destroy/clear will only wipe active_session_id when this session is still current.
        if ($request->routeIs('logout') || $request->is('logout')) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم تسجيل الدخول إلى حسابك من جهاز آخر.',
                'code' => 'single_session_replaced',
            ], 401);
        }

        return redirect()
            ->route('login')
            ->with('error', 'تم تسجيل الدخول إلى حسابك من جهاز آخر. أُنهيت هذه الجلسة لحماية حسابك.');
    }
}
