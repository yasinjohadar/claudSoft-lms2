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
        $groupRegistrationTerms = SiteSetting::query()
            ->where('key', 'group_registration_terms')
            ->value('value') ?? '';

        return view('admin.pages.settings.site.index', compact(
            'registrationEnabled',
            'forceProfileCompletion',
            'localDevLoginEnabled',
            'localDevLoginAvailable',
            'localDevLoginUrl',
            'groupRegistrationTerms',
        ));
    }

    /**
     * تحديث إعدادات الموقع
     */
    public function update(Request $request)
    {
        $request->validate([
            'group_registration_terms' => ['nullable', 'string', 'max:65000'],
        ]);

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

        SiteSetting::setValue(
            'group_registration_terms',
            (string) $request->input('group_registration_terms', ''),
            'شروط التسجيل العامة المعروضة في نماذج تسجيل المجموعات'
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
