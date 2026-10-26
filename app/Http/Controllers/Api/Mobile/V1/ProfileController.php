<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Enums\OtpPurpose;
use App\Http\Requests\Mobile\V1\UpdateAvatarRequest;
use App\Http\Requests\Mobile\V1\UpdateProfileRequest;
use App\Http\Requests\Student\ChangePasswordRequest;
use App\Http\Resources\Mobile\V1\UserResource;
use App\Models\Nationality;
use App\Services\Auth\PhoneOtpService;
use App\Services\Student\StudentProfilePhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * إدارة الملف الشخصي للطالب من تطبيق الجوال.
 */
class ProfileController extends BaseMobileController
{
    public function __construct(
        protected StudentProfilePhotoService $profilePhotoService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()->load('nationality')));
    }

    public function update(UpdateProfileRequest $request, PhoneOtpService $otpService): JsonResponse
    {
        $user = $request->user();

        $phoneChanged = $request->input('country_code') !== $user->country_code
            || $request->input('phone') !== $user->phone;

        // تغيير الهاتف يتطلب تحقق OTP عند تفعيله — يتم من الموقع حالياً.
        if ($phoneChanged && $otpService->isAvailableFor(OtpPurpose::ChangePhone)) {
            throw ValidationException::withMessages([
                'phone' => [__('mobile.phone_change_requires_web')],
            ]);
        }

        try {
            $user->fill([
                'name' => $request->input('name'),
                'name_ar' => $request->input('name_ar'),
                'country_code' => $request->input('country_code'),
                'phone' => $request->input('phone'),
                'date_of_birth' => $request->input('date_of_birth'),
                'gender' => $request->input('gender'),
                'city' => $request->input('city'),
                'address' => $request->input('address'),
                'nationality_id' => $request->input('nationality_id'),
            ])->save();
        } catch (\Throwable $e) {
            Log::error('Mobile API: profile update failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return $this->error(__('mobile.profile_update_failed'), 500);
        }

        return $this->success(
            new UserResource($user->refresh()->load('nationality')),
            __('mobile.profile_updated')
        );
    }

    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        $user = $request->user();
        $path = $this->profilePhotoService->store($user, $request->file('avatar'));

        if (! $path) {
            return $this->error(__('mobile.avatar_upload_failed'), 500);
        }

        $user->forceFill(['photo' => $path, 'avatar' => $path])->save();

        return $this->success(new UserResource($user->load('nationality')), __('mobile.avatar_updated'));
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->photo && ! $user->avatar) {
            return $this->error(__('mobile.avatar_missing'), 422);
        }

        $this->profilePhotoService->deleteForUser($user);
        $user->forceFill(['photo' => null, 'avatar' => null])->save();

        return $this->success(new UserResource($user->load('nationality')), __('mobile.avatar_deleted'));
    }

    /**
     * نفس سياسة لوحة الطالب: لا تُطلب كلمة المرور الحالية.
     * تُلغى جميع الجلسات الأخرى ويبقى التوكن الحالي فقط.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->password = Hash::make((string) $request->input('new_password'));
        $user->save();

        $currentTokenId = $user->currentAccessToken()?->id;
        $user->tokens()->when($currentTokenId, fn ($q) => $q->where('id', '!=', $currentTokenId))->delete();

        return $this->success(null, __('mobile.password_changed'));
    }

    public function nationalities(): JsonResponse
    {
        $list = Nationality::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($n) => ['id' => (int) $n->id, 'name' => (string) $n->name]);

        return $this->success($list);
    }
}
