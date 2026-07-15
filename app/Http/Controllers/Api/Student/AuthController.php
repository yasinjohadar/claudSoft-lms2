<?php

namespace App\Http\Controllers\Api\Student;

use App\Enums\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\PasswordCredentialDeliveryService;
use App\Services\Auth\PasswordResetDeliveryService;
use App\Services\Auth\PhoneOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

/**
 * مصادقة الطالب عبر API (للتطبيقات مثل Flutter).
 * إصدار توكن Sanctum بعد التحقق من البريد وكلمة المرور.
 */
class AuthController extends Controller
{
    /**
     * تسجيل الدخول: البريد + كلمة المرور، يُرجع توكن API للطالب فقط.
     */
    public function login(
        Request $request,
        \App\Services\DeviceAccessService $deviceAccessService,
    ): JsonResponse {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_token' => ['nullable', 'uuid'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (isset($user->is_active) && ! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => [__('الحساب غير مفعّل. تواصل مع الدعم.')],
            ]);
        }

        if (! $user->hasRole('student')) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => [__('هذا المسار مخصّص للطلاب فقط.')],
            ]);
        }

        $accessResult = $deviceAccessService->validateLoginDevice($user, $request);

        if (! $accessResult->isAllowed()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => [$accessResult->userMessage()],
            ]);
        }

        try {
            $deviceAccessService->completeAllowedLogin($user, $request, $accessResult);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to track device on API login', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // إصدار توكن للاستخدام من Flutter (يمكن تحديد صلاحيات لاحقاً)
        $token = $user->createToken('flutter-student')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'name_ar' => $user->name_ar ?? $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar ? url($user->avatar) : null,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * تسجيل الخروج: إبطال التوكن الحالي.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => __('تم تسجيل الخروج.'),
        ]);
    }

    /**
     * رموز الدول للنماذج (عام — بدون مصادقة).
     */
    public function countryCodes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->countryCodesPayload(),
        ]);
    }

    /**
     * خيارات استعادة كلمة المرور (قنوات متاحة ورموز الدول).
     */
    public function forgotPasswordOptions(
        PasswordResetDeliveryService $resetDelivery,
        PhoneOtpService $otpService
    ): JsonResponse {
        $whatsappAvailable = $resetDelivery->isWhatsAppAvailable();
        $whatsappOtpAvailable = $otpService->isAvailableFor(OtpPurpose::ResetPassword);

        $defaultChannel = $whatsappAvailable
            ? 'whatsapp'
            : ($whatsappOtpAvailable ? 'whatsapp_otp' : 'email');

        return response()->json([
            'success' => true,
            'data' => array_merge($this->countryCodesPayload(), [
                'whatsapp_available' => $whatsappAvailable,
                'whatsapp_otp_available' => $whatsappOtpAvailable,
                'default_channel' => $defaultChannel,
            ]),
        ]);
    }

    /**
     * @return array{default_country_code: string, country_codes: list<array{code: string, label: string, iso: string}>}
     */
    private function countryCodesPayload(): array
    {
        $list = config('country_codes.list_text_only', config('country_codes.list', []));
        $isoList = config('country_codes.iso', []);
        $countryCodes = [];
        foreach ($list as $code => $label) {
            $countryCodes[] = [
                'code' => $code,
                'label' => $label,
                'iso' => $isoList[$code] ?? '',
            ];
        }

        return [
            'default_country_code' => config('country_codes.default', '+963'),
            'country_codes' => $countryCodes,
        ];
    }

    /**
     * طلب رابط استعادة كلمة المرور عبر البريد أو الواتساب (Evolution / WhatsApp Web).
     */
    public function forgotPassword(
        Request $request,
        PasswordResetDeliveryService $resetDelivery,
        PhoneOtpService $otpService
    ): JsonResponse {
        $channel = (string) ($request->input('channel') ?: 'email');
        $countryCodes = array_keys(config('country_codes.list', []));
        $phoneChannels = ['whatsapp', 'whatsapp_otp'];

        $request->validate([
            'channel' => ['nullable', Rule::in(['email', 'whatsapp', 'whatsapp_otp'])],
            'email' => [Rule::requiredIf($channel === 'email'), 'nullable', 'email'],
            'country_code' => [
                Rule::requiredIf(in_array($channel, $phoneChannels, true)),
                'nullable',
                Rule::in($countryCodes),
            ],
            'phone' => [
                Rule::requiredIf(in_array($channel, $phoneChannels, true)),
                'nullable',
                'string',
                'regex:/^[0-9]{6,14}$/',
            ],
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'country_code.required' => 'رمز الدولة مطلوب.',
            'country_code.in' => 'رمز الدولة غير صالح.',
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'أدخل رقم الجوال بدون رمز الدولة وبدون صفر في البداية.',
        ]);

        if ($channel === 'whatsapp' && ! $resetDelivery->isWhatsAppAvailable()) {
            throw ValidationException::withMessages([
                'phone' => ['إرسال رابط الاستعادة عبر الواتساب غير متاح حالياً.'],
            ]);
        }

        if ($channel === 'whatsapp_otp' && ! $otpService->isAvailableFor(OtpPurpose::ResetPassword)) {
            throw ValidationException::withMessages([
                'phone' => ['استعادة كلمة المرور عبر رمز OTP غير متاحة حالياً.'],
            ]);
        }

        if ($channel === 'whatsapp_otp') {
            return $this->sendForgotPasswordOtp($request, $resetDelivery, $otpService);
        }

        try {
            if ($channel === 'whatsapp') {
                $result = $resetDelivery->sendViaWhatsAppParts(
                    (string) $request->input('country_code'),
                    (string) $request->input('phone')
                );
            } else {
                $result = $resetDelivery->sendViaEmail((string) $request->input('email'));
            }
        } catch (\InvalidArgumentException $e) {
            $field = $channel === 'whatsapp' ? 'phone' : 'email';

            throw ValidationException::withMessages([
                $field => [$e->getMessage()],
            ]);
        } catch (\Throwable $e) {
            report($e);

            $field = $channel === 'whatsapp' ? 'phone' : 'email';

            throw ValidationException::withMessages([
                $field => [$channel === 'whatsapp'
                    ? 'تعذّر إرسال بيانات الدخول عبر الواتساب. حاول لاحقاً أو استخدم البريد الإلكتروني.'
                    : 'تعذّر إرسال بيانات الدخول عبر البريد. تحقق من إعدادات SMTP في لوحة التحكم أو حاول لاحقاً.'],
            ]);
        }

        $status = $result['status'];
        $delivery = $result['delivery'];

        $messages = [
            Password::RESET_LINK_SENT => PasswordResetDeliveryService::buildSuccessMessage($delivery),
            Password::INVALID_USER => $channel === 'whatsapp'
                ? 'لا يمكننا العثور على حساب مرتبط بهذا الرقم.'
                : 'لا يمكننا العثور على مستخدم بهذا البريد الإلكتروني.',
            Password::RESET_THROTTLED => 'يرجى الانتظار قبل إعادة المحاولة.',
        ];

        $message = $messages[$status] ?? trans($status, [], 'ar');

        if ($status === Password::RESET_LINK_SENT) {
            $contact = $channel === 'whatsapp'
                ? ($delivery['whatsapp_recipient']
                    ?? $resetDelivery->formatPhoneForDisplay(
                        (string) $request->input('country_code'),
                        (string) $request->input('phone')
                    ))
                : (string) $request->input('email');

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'channel' => $channel,
                    'contact' => $contact,
                    'email_sent' => (bool) ($delivery['email_sent'] ?? false),
                    'whatsapp_sent' => (bool) ($delivery['whatsapp_sent'] ?? false),
                ],
            ]);
        }

        $field = $channel === 'whatsapp' ? 'phone' : 'email';

        throw ValidationException::withMessages([
            $field => [$message],
        ]);
    }

    /**
     * التحقق من رمز OTP لاستعادة كلمة المرور (بعد POST forgot-password بقناة whatsapp_otp).
     */
    public function verifyForgotPasswordOtp(Request $request, PhoneOtpService $otpService): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'min:4', 'max:8'],
        ], [
            'phone.required' => 'رقم الجوال مطلوب.',
            'code.required' => 'رمز التحقق مطلوب.',
        ]);

        try {
            $phone = $otpService->normalizePhone((string) $request->input('phone'));
            $otpService->verify($phone, OtpPurpose::ResetPassword, (string) $request->input('code'));
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'code' => [$e->getMessage()],
            ]);
        }

        $userId = Cache::get($this->passwordResetOtpCacheKey($phone));
        if (! $userId) {
            throw ValidationException::withMessages([
                'code' => ['انتهت الجلسة. أعد طلب الرمز.'],
            ]);
        }

        $user = User::query()->find($userId);
        if (! $user) {
            Cache::forget($this->passwordResetOtpCacheKey($phone));

            throw ValidationException::withMessages([
                'code' => ['تعذّر إكمال العملية.'],
            ]);
        }

        $token = Password::broker()->createToken($user);
        Cache::forget($this->passwordResetOtpCacheKey($phone));

        return response()->json([
            'success' => true,
            'message' => 'تم التحقق بنجاح. يمكنك الآن تعيين كلمة مرور جديدة.',
            'data' => [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ],
        ]);
    }

    /**
     * إعادة تعيين كلمة المرور بعد التحقق من OTP أو الرابط.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'token.required' => 'رمز إعادة التعيين مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'password.required' => 'كلمة المرور الجديدة مطلوبة.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        $plainPassword = (string) $request->password;

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        $messages = [
            Password::PASSWORD_RESET => 'تم إعادة تعيين كلمة المرور بنجاح.',
            Password::INVALID_USER => 'لا يمكننا العثور على مستخدم بهذا البريد الإلكتروني.',
            Password::INVALID_TOKEN => 'رمز إعادة تعيين كلمة المرور غير صحيح أو منتهي الصلاحية.',
            Password::RESET_THROTTLED => 'يرجى الانتظار قبل إعادة المحاولة.',
        ];

        $message = $messages[$status] ?? trans($status, [], 'ar');

        if ($status === Password::PASSWORD_RESET) {
            $user = User::query()->where('email', $request->email)->first();
            if ($user) {
                app(PasswordCredentialDeliveryService::class)->deliver(
                    $user,
                    $plainPassword,
                    PasswordCredentialDeliveryService::CONTEXT_FORGOT_MANUAL
                );
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [$message],
        ]);
    }

    private function sendForgotPasswordOtp(
        Request $request,
        PasswordResetDeliveryService $resetDelivery,
        PhoneOtpService $otpService
    ): JsonResponse {
        $fullPhone = $otpService->formatPhoneDisplay(
            (string) $request->input('country_code'),
            (string) $request->input('phone')
        );

        $user = $resetDelivery->findUserByPhone($fullPhone);
        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => ['لا يوجد حساب مرتبط بهذا الرقم.'],
            ]);
        }

        try {
            $result = $otpService->send(
                $fullPhone,
                OtpPurpose::ResetPassword,
                $user,
                $request->ip()
            );
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'phone' => [$e->getMessage()],
            ]);
        }

        $normalizedPhone = $otpService->normalizePhone($fullPhone);
        $ttlMinutes = max(1, (int) config('phone_otp.recent_verification_minutes', 15));

        Cache::put(
            $this->passwordResetOtpCacheKey($normalizedPhone),
            $user->id,
            now()->addMinutes($ttlMinutes)
        );

        $contact = $resetDelivery->formatPhoneForDisplay(
            (string) $request->input('country_code'),
            (string) $request->input('phone')
        );

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رمز التحقق إلى واتساب.',
            'data' => [
                'channel' => 'whatsapp_otp',
                'contact' => $contact,
                'phone' => $normalizedPhone,
                'cooldown_seconds' => $result['cooldown_seconds'],
                'expires_at' => $result['expires_at'],
            ],
        ]);
    }

    private function passwordResetOtpCacheKey(string $normalizedPhone): string
    {
        return 'api_password_reset_otp_user:'.$normalizedPhone;
    }

    /**
     * المستخدم الحالي (للتحقق من التوكن).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('nationality');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'name_ar' => $user->name_ar ?? $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'country_code' => $user->country_code,
                    'student_id' => $user->student_id,
                    'national_id' => $user->national_id,
                    'nationality_id' => $user->nationality_id,
                    'nationality_name' => $user->nationality?->name,
                    'date_of_birth' => $user->date_of_birth
                        ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d')
                        : null,
                    'gender' => $user->gender,
                    'address' => $user->address,
                    'is_profile_public' => (bool) $user->is_profile_public,
                    'last_login_at' => $user->last_login_at
                        ? \Carbon\Carbon::parse($user->last_login_at)->toIso8601String()
                        : null,
                    'avatar' => $user->avatar ? url($user->avatar) : null,
                ],
            ],
        ]);
    }
}
