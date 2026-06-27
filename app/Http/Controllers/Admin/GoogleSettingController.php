<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleSetting;
use App\Services\Marketing\MarketingAnalyticsService;
use Illuminate\Http\Request;

class GoogleSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function edit()
    {
        $settings = GoogleSetting::getSettings();

        $stats = [
            'gtm_active' => $settings->isGtmActive(),
            'gsc_active' => $settings->isSearchConsoleActive(),
            'api_active' => $settings->isAnalyticsApiActive(),
            'gsc_api_active' => $settings->isSearchConsoleApiActive(),
        ];

        return view('admin.pages.google-settings.edit', compact('settings', 'stats'));
    }

    public function update(Request $request)
    {
        $settings = GoogleSetting::getSettings();

        $validated = $request->validate([
            'gtm_container_id' => 'nullable|string|regex:/^GTM-[A-Z0-9]+$/',
            'gtm_enabled' => 'boolean',
            'search_console_verification' => 'nullable|string|max:255',
            'search_console_enabled' => 'boolean',
            'ga4_property_id' => 'nullable|string|regex:/^\d+$/',
            'gsc_site_url' => 'nullable|string|max:255',
            'service_account_json' => 'nullable|string|max:10000',
            'analytics_api_enabled' => 'boolean',
            'analytics_cache_minutes' => 'nullable|integer|min:5|max:1440',
        ]);

        $updateData = [
            'gtm_container_id' => $validated['gtm_container_id'] ?? null,
            'gtm_enabled' => $request->boolean('gtm_enabled'),
            'search_console_verification' => $validated['search_console_verification'] ?? null,
            'search_console_enabled' => $request->boolean('search_console_enabled'),
            'ga4_property_id' => $validated['ga4_property_id'] ?? null,
            'gsc_site_url' => $validated['gsc_site_url'] ?? null,
            'analytics_api_enabled' => $request->boolean('analytics_api_enabled'),
            'analytics_cache_minutes' => $validated['analytics_cache_minutes'] ?? 60,
        ];

        if ($request->filled('service_account_json')) {
            $json = trim($request->input('service_account_json'));
            json_decode($json);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withInput()->withErrors([
                    'service_account_json' => 'ملف Service Account يجب أن يكون JSON صالحاً',
                ]);
            }
            $updateData['service_account_json'] = $json;
        }

        $settings->update($updateData);

        return redirect()
            ->route('admin.google-settings.edit')
            ->with('success', 'تم تحديث إعدادات Google بنجاح');
    }

    public function testApi(MarketingAnalyticsService $analytics)
    {
        $result = $analytics->testConnection();

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
