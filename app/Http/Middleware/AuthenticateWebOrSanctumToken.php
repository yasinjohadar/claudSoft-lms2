<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * للمتصفح: جلسة web. للـ WebView/API: Bearer بعد auth.query_token.
 */
class AuthenticateWebOrSanctumToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check()) {
            Auth::shouldUse('web');

            return $next($request);
        }

        if ($request->bearerToken()) {
            $user = Auth::guard('sanctum')->user();
            if ($user) {
                Auth::guard('web')->setUser($user);
                Auth::shouldUse('web');

                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            abort(401, 'Unauthenticated.');
        }

        return redirect()->guest(route('login'));
    }
}
