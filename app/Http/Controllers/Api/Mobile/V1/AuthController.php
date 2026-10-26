<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Requests\Mobile\V1\LoginRequest;
use App\Http\Resources\Mobile\V1\UserResource;
use App\Models\User;
use App\Services\DeviceAccessService;
use App\Support\CredentialPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * مصادقة تطبيق الجوال (للطلاب فقط) عبر توكنات Sanctum.
 */
class AuthController extends BaseMobileController
{
    public function login(LoginRequest $request, DeviceAccessService $deviceAccessService): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();
        $password = CredentialPassword::sanitizeForAuth((string) $request->input('password'));

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->hasRole('student')) {
            return $this->error(__('mobile.students_only'), 403);
        }

        if (! $user->is_active) {
            return $this->error(__('mobile.account_inactive'), 403);
        }

        $accessResult = $deviceAccessService->validateLoginDevice($user, $request);

        if (! $accessResult->isAllowed()) {
            return $this->error($accessResult->userMessage(), 403);
        }

        try {
            $deviceAccessService->completeAllowedLogin($user, $request, $accessResult);
        } catch (\Throwable $e) {
            Log::error('Mobile API: failed to track device on login', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $deviceName = $request->input('device_name') ?: 'device';
        $token = $user->createToken('mobile:'.$deviceName)->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user->load('nationality')))->resolve($request),
        ], __('mobile.login_success'));
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()->load('nationality')));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->success(null, __('mobile.logout_success'));
    }
}
