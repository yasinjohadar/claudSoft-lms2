<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordResetDeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
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
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $user = Auth::user();

        if (isset($user->is_active) && !$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => [__('الحساب غير مفعّل. تواصل مع الدعم.')],
            ]);
        }

        if (!$user->hasRole('student')) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => [__('هذا المسار مخصّص للطلاب فقط.')],
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
    public function forgotPasswordOptions(PasswordResetDeliveryService $resetDelivery): JsonResponse
    {
        $whatsappAvailable = $resetDelivery->isWhatsAppAvailable();

        return response()->json([
            'success' => true,
            'data' => array_merge($this->countryCodesPayload(), [
                'whatsapp_available' => $whatsappAvailable,
                'default_channel' => 'whatsapp',
            ]),
        ]);
    }

    /**
     * @return array{default_country_code: string, country_codes: list<array{code: string, label: string}>}
     */
    private function countryCodesPayload(): array
    {
        $list = config('country_codes.list_text_only', config('country_codes.list', []));
        $countryCodes = [];
        foreach ($list as $code => $label) {
            $countryCodes[] = [
                'code' => $code,
                'label' => $label,
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
    public function forgotPassword(Request $request, PasswordResetDeliveryService $resetDelivery): JsonResponse
    {
        $channel = (string) ($request->input('channel') ?: 'email');
        $countryCodes = array_keys(config('country_codes.list', []));

        $request->validate([
            'channel' => ['nullable', Rule::in(['email', 'whatsapp'])],
            'email' => [Rule::requiredIf($channel === 'email'), 'nullable', 'email'],
            'country_code' => [
                Rule::requiredIf($channel === 'whatsapp'),
                'nullable',
                Rule::in($countryCodes),
            ],
            'phone' => [
                Rule::requiredIf($channel === 'whatsapp'),
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

        try {
            if ($channel === 'whatsapp') {
                $status = $resetDelivery->sendViaWhatsAppParts(
                    (string) $request->input('country_code'),
                    (string) $request->input('phone')
                );
            } else {
                $status = $resetDelivery->sendViaEmail((string) $request->input('email'));
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
                    ? 'تعذّر إرسال رابط الاستعادة عبر الواتساب. حاول لاحقاً أو استخدم البريد الإلكتروني.'
                    : 'تعذّر إرسال رابط الاستعادة عبر البريد. تحقق من إعدادات SMTP في لوحة التحكم أو حاول لاحقاً.'],
            ]);
        }

        $messages = [
            Password::RESET_LINK_SENT => $channel === 'whatsapp'
                ? 'تم إرسال رابط إعادة تعيين كلمة المرور إلى واتسابك.'
                : 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.',
            Password::INVALID_USER => $channel === 'whatsapp'
                ? 'لا يمكننا العثور على حساب مرتبط بهذا الرقم.'
                : 'لا يمكننا العثور على مستخدم بهذا البريد الإلكتروني.',
            Password::RESET_THROTTLED => 'يرجى الانتظار قبل إعادة المحاولة.',
        ];

        $message = $messages[$status] ?? trans($status, [], 'ar');

        if ($status === Password::RESET_LINK_SENT) {
            $contact = $channel === 'whatsapp'
                ? $resetDelivery->formatPhoneForDisplay(
                    (string) $request->input('country_code'),
                    (string) $request->input('phone')
                )
                : (string) $request->input('email');

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'channel' => $channel,
                    'contact' => $contact,
                ],
            ]);
        }

        $field = $channel === 'whatsapp' ? 'phone' : 'email';

        throw ValidationException::withMessages([
            $field => [$message],
        ]);
    }

    /**
     * المستخدم الحالي (للتحقق من التوكن).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

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
            ],
        ]);
    }
}
