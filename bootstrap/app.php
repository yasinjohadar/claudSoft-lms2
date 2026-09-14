<?php

use App\Http\Middleware\AcceptTokenFromQueryParam;
use App\Http\Middleware\AuthenticateWebOrSanctumToken;
use App\Http\Middleware\CheckDeviceBlocked;
use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\EnforceSessionDeviceBinding;
use App\Http\Middleware\EnforceSingleSession;
use App\Http\Middleware\EnsureLocalDevLoginAvailable;
use App\Http\Middleware\EnsurePublicRegistrationEnabled;
use App\Http\Middleware\EnsureRecentPhoneVerification;
use App\Http\Middleware\ImpersonateMiddleware;
use App\Http\Middleware\LogStudentApiRequests;
use App\Http\Middleware\OptionalSanctumAuth;
use App\Http\Middleware\ParseMultipartFormData;
use App\Http\Middleware\RequireCompleteStudentProfile;
use App\Http\Middleware\SessionTrackingMiddleware;
use App\Http\Middleware\SetApplicationLocale;
use App\Providers\StorageHelperServiceProvider;
use App\Providers\StorageServiceProvider;
use App\Support\SessionExpiredRedirect;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // طلبات /api/* يجب ألا تُحوَّل لصفحة login (خصوصاً مشغّل الفيديو HTML داخل iframe الديسكتوب)
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->is('api')) {
                return null;
            }

            return '/login';
        });

        $middleware->alias([
            'auth.query_token' => AcceptTokenFromQueryParam::class,
            'auth.token' => AcceptTokenFromQueryParam::class,
            'auth.web_or_sanctum' => AuthenticateWebOrSanctumToken::class,
            'log.student.api' => LogStudentApiRequests::class,
            'optional.sanctum' => OptionalSanctumAuth::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'role-list' => PermissionMiddleware::class,
            'check.user.active' => CheckUserActive::class,
            'check.device.blocked' => CheckDeviceBlocked::class,
            'single.session' => EnforceSingleSession::class,
            'session.device.binding' => EnforceSessionDeviceBinding::class,
            'impersonate' => ImpersonateMiddleware::class,
            'student.profile.complete' => RequireCompleteStudentProfile::class,
            'phone.verified.recent' => EnsureRecentPhoneVerification::class,
            'local.dev.login' => EnsureLocalDevLoginAvailable::class,
            'public.registration' => EnsurePublicRegistrationEnabled::class,
        ]);

        // Add middleware to parse multipart/form-data for PUT/PATCH requests - PREPEND to run first
        $middleware->web(prepend: [
            ParseMultipartFormData::class,
            SetApplicationLocale::class,
        ]);

        $middleware->api(prepend: [
            SetApplicationLocale::class,
        ]);

        // Sanctum: السماح بجلسة المتصفح على نفس النطاق (claudsoft.com وغيره)
        $middleware->statefulApi();

        // Add impersonate middleware to web group to share data with views
        $middleware->web(append: [
            ImpersonateMiddleware::class,
        ]);

        // Add session tracking middleware to track user sessions and activities
        $middleware->web(append: [
            SessionTrackingMiddleware::class,
            CheckDeviceBlocked::class,
            EnforceSingleSession::class,
            EnforceSessionDeviceBinding::class,
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
        StorageServiceProvider::class,
        StorageHelperServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // منع تحويل /api/* إلى HTML login عند فشل التوكن (مشغّل الديسكتوب يطلب Accept: text/html)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->is('api')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'data' => null,
                ], 401);
            }

            return null;
        });

        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                $redirect = SessionExpiredRedirect::resolve($request);

                return response()->json([
                    'message' => 'انتهت صلاحية الجلسة. يرجى تحديث الصفحة والمحاولة مرة أخرى.',
                    'code' => 'session_expired',
                    'redirect' => $redirect['url'],
                ], 419);
            }

            return response()->view('errors.419', [
                'redirect' => SessionExpiredRedirect::resolve($request),
            ], 419);
        });
    })->create();
