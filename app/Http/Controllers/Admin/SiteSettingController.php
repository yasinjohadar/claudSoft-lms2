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
        
        return view('admin.pages.settings.site.index', compact('registrationEnabled'));
    }

    /**
     * تحديث إعدادات الموقع
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'registration_public_enabled' => 'required|boolean',
        ]);

        SiteSetting::setValue(
            'registration_public_enabled',
            $validated['registration_public_enabled'],
            'تفعيل/إيقاف التسجيل العام للزوار (صفحة /register)'
        );

        return redirect()
            ->route('admin.settings.site.index')
            ->with('success', 'تم تحديث إعدادات الموقع بنجاح');
    }
}
