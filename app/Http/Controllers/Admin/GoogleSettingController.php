<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleSetting;
use Illuminate\Http\Request;

class GoogleSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * عرض صفحة تعديل إعدادات Google
     */
    public function edit()
    {
        $settings = GoogleSetting::getSettings();
        return view('admin.pages.google-settings.edit', compact('settings'));
    }

    /**
     * تحديث إعدادات Google
     */
    public function update(Request $request)
    {
        $settings = GoogleSetting::getSettings();

        $validated = $request->validate([
            'gtm_container_id' => 'nullable|string|regex:/^GTM-[A-Z0-9]+$/',
            'gtm_enabled' => 'boolean',
            'search_console_verification' => 'nullable|string|max:255',
            'search_console_enabled' => 'boolean',
        ]);

        $settings->update($validated);

        return redirect()->route('admin.google-settings.edit')
            ->with('success', 'تم تحديث إعدادات Google بنجاح');
    }
}
