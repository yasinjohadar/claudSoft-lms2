<?php

namespace App\Http\Middleware;

use App\Support\LocalDevLoginGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLocalDevLoginAvailable
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!LocalDevLoginGate::isAvailable()) {
            abort(404);
        }

        return $next($request);
    }
}
