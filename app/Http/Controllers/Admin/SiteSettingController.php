<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\LocalDevLoginGate;
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
        $localDevLoginEnabled = SiteSetting::isLocalDevLoginEnabled();
        $localDevLoginAvailable = LocalDevLoginGate::isEnvironmentLocal();
        $localDevLoginUrl = $localDevLoginAvailable ? LocalDevLoginGate::url() : null;

        return view('admin.pages.settings.site.index', compact(
            'registrationEnabled',
            'forceProfileCompletion',
            'localDevLoginEnabled',
            'localDevLoginAvailable',
            'localDevLoginUrl',
        ));
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

        if (LocalDevLoginGate::isEnvironmentLocal()) {
            $localDevLoginEnabled = $request->has('local_dev_login_enabled')
                && $request->input('local_dev_login_enabled') == '1';

            SiteSetting::setValue(
                'local_dev_login_enabled',
                $localDevLoginEnabled,
                'تفعيل صفحة الدخول السريع للتطوير المحلي (local فقط)'
            );
        }

        return redirect()
            ->route('admin.settings.site.index')
            ->with('success', 'تم تحديث إعدادات الموقع بنجاح');
    }
}
