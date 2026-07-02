<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ImpersonationToken;
use App\Services\Admin\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth')->only(['impersonate', 'stop']);
        // فقط impersonate يحتاج role:admin
        $this->middleware('role:admin')->only('impersonate');
        // loginWithToken لا يحتاج auth لأنه سيسجل الدخول
    }

    /**
     * Start impersonating a user - إنشاء token وإرجاع رابط
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function impersonate(User $user)
    {
        // التحقق من أن المستخدم الحالي أدمن
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'غير مصرح لك بالدخول إلى حسابات المستخدمين');
        }

        // منع الأدمن من الدخول إلى حسابات أدمن آخرين
        if ($user->hasRole('admin')) {
            return back()->with('error', 'لا يمكنك الدخول إلى حسابات الأدمن الآخرين');
        }

        // التحقق من أن المستخدم نشط
        if (!$user->is_active) {
            return back()->with('error', 'لا يمكنك الدخول إلى حساب غير نشط');
        }

        // إنشاء token جديد
        $token = ImpersonationToken::createToken(Auth::id(), $user->id, 60); // صلاحية 60 دقيقة

        // إرجاع رابط للفتح في تبويب جديد
        $impersonateUrl = route('impersonate.login', ['token' => $token->token]);

        // إذا كان الطلب AJAX، إرجاع JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'url' => $impersonateUrl,
                'message' => 'تم إنشاء رابط الدخول بنجاح'
            ]);
        }

        // إرجاع رابط للفتح في تبويب جديد
        return back()->with('impersonate_url', $impersonateUrl)
            ->with('success', 'تم إنشاء رابط الدخول. سيتم فتحه في تبويب جديد');
    }

    /**
     * تسجيل الدخول باستخدام token
     *
     * @param  string  $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginWithToken(string $token)
    {
        // البحث عن token صالح
        $impersonationToken = ImpersonationToken::findValidToken($token);

        if (!$impersonationToken) {
            return redirect()->route('login')
                ->with('error', 'رابط الدخول غير صالح أو منتهي الصلاحية');
        }

        // التحقق من أن الأدمن الأصلي ما زال نشطاً
        $admin = $impersonationToken->admin;
        if (!$admin || !$admin->is_active || !$admin->hasRole('admin')) {
            $impersonationToken->markAsUsed();
            return redirect()->route('login')
                ->with('error', 'الأدمن الذي أنشأ هذا الرابط لم يعد نشطاً');
        }

        // التحقق من أن المستخدم المراد الدخول إليه ما زال نشطاً
        $user = $impersonationToken->user;
        if (!$user || !$user->is_active) {
            $impersonationToken->markAsUsed();
            return redirect()->route('login')
                ->with('error', 'المستخدم المراد الدخول إليه لم يعد نشطاً');
        }

        // تحديد Token كمستخدم
        $impersonationToken->markAsUsed();

        // حفظ معلومات Impersonation في متغير مؤقت قبل تسجيل الخروج
        $impersonateData = [
            'original_user_id' => $admin->id,
            'original_user_name' => $admin->name,
            'impersonated_at' => now(),
            'token_id' => $impersonationToken->id,
        ];

        // تسجيل خروج أي مستخدم مسجل دخول حالياً (لضمان نظافة الجلسة)
        if (Auth::check()) {
            Auth::logout();
        }

        // تنظيف Session ولكن نحتفظ بالبيانات الأساسية
        Session::invalidate();
        Session::regenerateToken();

        // حفظ معلومات Impersonation في Session الجديدة
        Session::put('impersonate', $impersonateData);

        // تسجيل الدخول كالمستخدم المطلوب (الطالب)
        Auth::login($user, false); // false = لا تذكرني

        ActivityLogService::logImpersonationStarted($admin, $user);

        // إعادة توليد Session ID للأمان
        Session::regenerate();

        // التحقق النهائي من أننا سجلنا الدخول كالطالب الصحيح
        if (Auth::id() !== $user->id) {
            Auth::logout();
            Session::forget('impersonate');
            return redirect()->route('login')
                ->with('error', 'حدث خطأ أثناء تسجيل الدخول. يرجى المحاولة مرة أخرى');
        }

        return redirect()->route('student.dashboard')
            ->with('success', 'تم الدخول كـ ' . $user->name . ' بنجاح');
    }

    /**
     * Stop impersonating and return to original admin account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stop()
    {
        // التحقق من وجود حالة Impersonation
        if (!Session::has('impersonate')) {
            // إذا لم تكن هناك حالة Impersonation، ربما المستخدم في التبويب الأصلي
            // إعادة توجيه إلى لوحة الأدمن
            if (Auth::check() && Auth::user()->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('login')
                ->with('error', 'لا توجد حالة دخول نشطة');
        }

        $impersonateData = Session::get('impersonate');
        $originalUserId = $impersonateData['original_user_id'];
        $impersonatedUser = Auth::user();

        // الحصول على الأدمن الأصلي
        $originalUser = User::findOrFail($originalUserId);

        // التحقق من أن الأدمن الأصلي ما زال موجوداً ونشطاً
        if (!$originalUser || !$originalUser->is_active) {
            Session::forget('impersonate');
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'تم إلغاء حالة الدخول. يرجى تسجيل الدخول مرة أخرى');
        }

        // حذف بيانات Impersonation من Session
        Session::forget('impersonate');

        ActivityLogService::logImpersonationStopped(
            $originalUser,
            $impersonatedUser instanceof User ? $impersonatedUser : null
        );

        // تسجيل الدخول كالأدمن الأصلي
        Auth::login($originalUser);

        return redirect()->route('admin.dashboard')
            ->with('success', 'تم العودة إلى حسابك بنجاح');
    }
}

