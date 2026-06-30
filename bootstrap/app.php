<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
       $middleware->alias([
            'auth.query_token' => \App\Http\Middleware\AcceptTokenFromQueryParam::class,
            'auth.token' => \App\Http\Middleware\AcceptTokenFromQueryParam::class,
            'auth.web_or_sanctum' => \App\Http\Middleware\AuthenticateWebOrSanctumToken::class,
            'log.student.api' => \App\Http\Middleware\LogStudentApiRequests::class,
            'optional.sanctum' => \App\Http\Middleware\OptionalSanctumAuth::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'role-list' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'check.user.active' => \App\Http\Middleware\CheckUserActive::class,
            'webhook.verify' => \App\Http\Middleware\VerifyWebhookSignature::class,
            'impersonate' => \App\Http\Middleware\ImpersonateMiddleware::class,
            'student.profile.complete' => \App\Http\Middleware\RequireCompleteStudentProfile::class,
            'phone.verified.recent' => \App\Http\Middleware\EnsureRecentPhoneVerification::class,
            'local.dev.login' => \App\Http\Middleware\EnsureLocalDevLoginAvailable::class,
            'public.registration' => \App\Http\Middleware\EnsurePublicRegistrationEnabled::class,
        ]);

        // Add middleware to parse multipart/form-data for PUT/PATCH requests - PREPEND to run first
        $middleware->web(prepend: [
            \App\Http\Middleware\ParseMultipartFormData::class,
            \App\Http\Middleware\SetApplicationLocale::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\SetApplicationLocale::class,
        ]);

        // Sanctum: السماح بجلسة المتصفح على نفس النطاق (claudsoft.com وغيره)
        $middleware->statefulApi();

        // Add impersonate middleware to web group to share data with views
        $middleware->web(append: [
            \App\Http\Middleware\ImpersonateMiddleware::class,
        ]);
        
        // Add session tracking middleware to track user sessions and activities
        $middleware->web(append: [
            \App\Http\Middleware\SessionTrackingMiddleware::class,
        ]);
        
        // Add debug middleware for question modules (only in debug mode and not in production)
        // Disabled by default to avoid potential issues - enable manually if needed for debugging
        // if (config('app.debug') && config('app.env') !== 'production') {
        //     $middleware->web(append: [
        //         \App\Http\Middleware\DebugQuestionModuleRoute::class,
        //     ]);
        // }
    })
    ->withEvents(discover: [
        __DIR__.'/../app/Listeners',
    ])
    ->withProviders([
        App\Providers\StorageServiceProvider::class,
        App\Providers\StorageHelperServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                $redirect = \App\Support\SessionExpiredRedirect::resolve($request);

                return response()->json([
                    'message' => 'انتهت صلاحية الجلسة. يرجى تحديث الصفحة والمحاولة مرة أخرى.',
                    'code' => 'session_expired',
                    'redirect' => $redirect['url'],
                ], 419);
            }

            return response()->view('errors.419', [
                'redirect' => \App\Support\SessionExpiredRedirect::resolve($request),
            ], 419);
        });
    })->create();