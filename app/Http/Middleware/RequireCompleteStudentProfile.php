<?php

namespace App\Http\Middleware;

use App\Services\Student\StudentProfileCompletionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCompleteStudentProfile
{
    public function __construct(
        protected StudentProfileCompletionService $profileCompletion,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->profileCompletion->isEnforcementEnabled()) {
            return $next($request);
        }

        $user = $request->user();

        if ($this->profileCompletion->shouldBypass($request, $user)) {
            return $next($request);
        }

        if ($this->profileCompletion->isComplete($user)) {
            return $next($request);
        }

        view()->share('studentProfileLocked', true);

        $routeName = $request->route()?->getName();
        $isApi = $request->is('api/*') || $request->expectsJson();

        if ($this->profileCompletion->isAllowedRoute($routeName, $isApi)) {
            return $next($request);
        }

        if ($isApi) {
            return response()->json(
                $this->profileCompletion->buildApiBlockPayload($user),
                Response::HTTP_FORBIDDEN
            );
        }

        return redirect()
            ->route('student.profile.edit')
            ->with('warning', $this->profileCompletion->getRedirectMessage())
            ->with('profile_completion_required', true);
    }
}
