<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicRegistrationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (SiteSetting::isPublicRegistrationEnabled()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'التسجيل العام معطل حالياً. يرجى التواصل مع الإدارة أو استخدام حساب موجود.',
            ], 403);
        }

        return redirect()
            ->route('login')
            ->with('error', 'التسجيل العام معطل حالياً. يرجى التواصل مع الإدارة أو استخدام حساب موجود.');
    }
}
