<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class ImpersonateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // مشاركة معلومات Impersonation مع جميع الـ views
        if (Session::has('impersonate')) {
            $impersonateData = Session::get('impersonate');
            view()->share('isImpersonating', true);
            view()->share('originalUser', \App\Models\User::find($impersonateData['original_user_id']));
            view()->share('impersonateData', $impersonateData);
        } else {
            view()->share('isImpersonating', false);
        }

        return $next($request);
    }
}

