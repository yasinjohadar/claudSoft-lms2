<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugQuestionModuleRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only debug question-module routes
        if (str_contains($request->path(), 'question-modules')) {
            Log::info('=== DEBUG: Question Module Route Hit ===', [
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'route_name' => $request->route()?->getName(),
                'route_action' => $request->route()?->getActionName(),
                'route_parameters' => $request->route()?->parameters(),
                'user_id' => auth()->id(),
                'user_authenticated' => auth()->check(),
            ]);
        }

        $response = $next($request);

        // Log response if it's a redirect
        if ($response->isRedirection() && str_contains($request->path(), 'question-modules')) {
            Log::info('=== DEBUG: Question Module Route Redirect ===', [
                'redirect_url' => $response->getTargetUrl(),
                'status_code' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }
}

