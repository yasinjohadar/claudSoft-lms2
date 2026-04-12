<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Injects Authorization: Bearer from ?token= when no Bearer header is present.
 * Scoped routes only (e.g. /docs for WebView). Do not log token values.
 */
class AcceptTokenFromQueryParam
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $existing = $request->bearerToken();
        if ($existing !== null && $existing !== '') {
            return $next($request);
        }

        if (! $request->has('token')) {
            return $next($request);
        }

        $token = $request->query('token');

        if (! is_string($token)) {
            return $next($request);
        }

        $token = str_replace(["\r", "\n"], '', trim($token));

        if ($token === '') {
            return $next($request);
        }

        $request->headers->set('Authorization', 'Bearer '.$token);

        return $next($request);
    }
}
