<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use Illuminate\Http\Request;

class ContactSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * عرض صفحة تعديل إعدادات الاتصال
     */
    public function edit()
    {
        $settings = ContactSetting::getSettings();
        return view('admin.pages.contact-settings.edit', compact('settings'));
    }

    /**
     * تحديث إعدادات الاتصال
     */
    public function update(Request $request)
    {
        $settings = ContactSetting::getSettings();

        $validated = $request->validate([
            'page_title' => 'required|string|max:255',
            'page_subtitle' => 'nullable|string|max:500',
            'address_title' => 'required|string|max:255',
            'address_text' => 'nullable|string',
            'address_icon' => 'nullable|string|max:50',
            'phone_title' => 'required|string|max:255',
            'phone_numbers' => 'nullable|array',
            'phone_numbers.*.number' => 'nullable|string|max:50',
            'phone_numbers.*.label' => 'nullable|string|max:100',
            'phone_icon' => 'nullable|string|max:50',
            'email_title' => 'required|string|max:255',
            'email_addresses' => 'nullable|array',
            'email_addresses.*.email' => 'nullable|email|max:255',
            'email_addresses.*.label' => 'nullable|string|max:100',
            'email_icon' => 'nullable|string|max:50',
            'map_embed_url' => 'nullable|string',
            'show_map' => 'boolean',
            'social_title' => 'required|string|max:255',
            'social_subtitle' => 'nullable|string|max:500',
            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'nullable|string|max:50',
            'social_links.*.url' => 'nullable|url|max:500',
            'social_links.*.icon' => 'nullable|string|max:50',
            'social_links.*.label' => 'nullable|string|max:100',
            'social_links.*.enabled' => 'boolean',
            'working_hours_title' => 'required|string|max:255',
            'working_hours' => 'nullable|array',
            'working_hours.*.day' => 'nullable|string|max:100',
            'working_hours.*.time' => 'nullable|string|max:100',
            'show_working_hours' => 'boolean',
            'form_title' => 'required|string|max:255',
            'form_subtitle' => 'nullable|string|max:500',
            'form_enabled' => 'boolean',
        ]);

        // تنظيف المصفوفات الفارغة
        if (isset($validated['phone_numbers'])) {
            $validated['phone_numbers'] = array_filter($validated['phone_numbers'], function($item) {
                return !empty($item['number']);
            });
        }

        if (isset($validated['email_addresses'])) {
            $validated['email_addresses'] = array_filter($validated['email_addresses'], function($item) {
                return !empty($item['email']);
            });
        }

        if (isset($validated['social_links'])) {
            $validated['social_links'] = array_filter($validated['social_links'], function($item) {
                return !empty($item['platform']) && !empty($item['url']);
            });
        }

        if (isset($validated['working_hours'])) {
            $validated['working_hours'] = array_filter($validated['working_hours'], function($item) {
                return !empty($item['day']) && !empty($item['time']);
            });
        }

        $settings->update($validated);

        return redirect()->route('admin.contact-settings.edit')
            ->with('success', 'تم تحديث إعدادات صفحة الاتصال بنجاح');
    }
}
