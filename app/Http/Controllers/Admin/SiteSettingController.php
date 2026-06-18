<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    /**
     * عرض صفحة إعدادات الموقع
     */
    public function index()
    {
        $registrationEnabled = SiteSetting::isPublicRegistrationEnabled();
        $forceProfileCompletion = SiteSetting::isStudentProfileCompletionForced();

        return view('admin.pages.settings.site.index', compact('registrationEnabled', 'forceProfileCompletion'));
    }

    /**
     * تحديث إعدادات الموقع
     */
    public function update(Request $request)
    {
        // التحقق من وجود الحقل (checkbox غير محدد = false)
        $registrationEnabled = $request->has('registration_public_enabled') && $request->input('registration_public_enabled') == '1';
        $forceProfileCompletion = $request->has('force_student_profile_completion') && $request->input('force_student_profile_completion') == '1';

        SiteSetting::setValue(
            'registration_public_enabled',
            $registrationEnabled,
            'تفعيل/إيقاف التسجيل العام للزوار (صفحة /register)'
        );

        SiteSetting::setValue(
            'force_student_profile_completion',
            $forceProfileCompletion,
            'إجبار الطلاب على إكمال ملفهم الشخصي 100% قبل استخدام المنصة'
        );

        return redirect()
            ->route('admin.settings.site.index')
            ->with('success', 'تم تحديث إعدادات الموقع بنجاح');
    }
}
