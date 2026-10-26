<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * تطبيق الجوال مخصّص للطلاب فقط: يرفض غير الطلاب والحسابات المعطّلة باستجابة JSON.
 */
class EnsureMobileStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('student')) {
            return response()->json([
                'success' => false,
                'message' => __('mobile.students_only'),
                'data' => null,
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $user->is_active) {
            $user->currentAccessToken()?->delete();

            return response()->json([
                'success' => false,
                'message' => __('mobile.account_inactive'),
                'data' => null,
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
