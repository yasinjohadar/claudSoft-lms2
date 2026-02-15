<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
