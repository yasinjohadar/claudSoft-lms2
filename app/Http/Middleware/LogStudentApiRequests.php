<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * تسجيل طلبات API الطالب لمعرفة إن كان الطلب يصل للسيرفر وما إن كان التوكن مُرسَلاً.
 */
class LogStudentApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $hasAuth = $request->hasHeader('Authorization');
        $authPrefix = $hasAuth && str_starts_with((string) $request->header('Authorization'), 'Bearer ');

        Log::channel('single')->info('[Student API] request', [
            'path' => $request->path(),
            'method' => $request->method(),
            'has_authorization_header' => $hasAuth,
            'has_bearer_prefix' => $authPrefix,
            'origin' => $request->header('Origin'),
        ]);

        $response = $next($request);

        Log::channel('single')->info('[Student API] response', [
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
