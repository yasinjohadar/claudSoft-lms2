<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يحمّل مستخدم Sanctum من Bearer إن وُجد، دون إرجاع 401 عند غياب التوكن.
 */
class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken()) {
            $user = auth('sanctum')->user();
            if ($user) {
                $request->setUserResolver(static fn () => $user);
            }
        }

        return $next($request);
    }
}
