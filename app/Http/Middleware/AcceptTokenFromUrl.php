<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Injects Authorization: Bearer from ?token= when no Bearer header is present.
 * For WebView / clients that cannot send headers (use only on scoped routes).
 */
class AcceptTokenFromUrl
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken() !== null && $request->bearerToken() !== '') {
            return $next($request);
        }

        $token = $request->query('token');

        if (is_string($token) && $token !== '') {
            $request->headers->set('Authorization', 'Bearer '.$token);
        }

        return $next($request);
    }
}
