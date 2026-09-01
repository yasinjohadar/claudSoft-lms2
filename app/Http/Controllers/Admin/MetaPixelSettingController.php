<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetaPixelSetting;
use App\Services\Marketing\MetaPixelService;
use Illuminate\Http\Request;

class MetaPixelSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function edit()
    {
        $settings = MetaPixelSetting::getSettings();
        $events = config('meta_pixel.events', []);

        $stats = [
            'pixel_active' => $settings->hasValidPixel(),
            'capi_active' => $settings->hasValidCapi(),
            'events_enabled' => $settings->enabledEventsCount(),
            'events_total' => count($events),
        ];

        return view('admin.pages.meta-pixel-settings.edit', compact('settings', 'events', 'stats'));
    }

    public function update(Request $request)
    {
        $settings = MetaPixelSetting::getSettings();

        $validated = $request->validate([
            'pixel_id' => 'nullable|string|regex:/^\d+$/',
            'capi_access_token' => 'nullable|string|max:2000',
            'test_event_code' => 'nullable|string|max:255',
        ]);

        $updateData = [
            'pixel_id' => $validated['pixel_id'] ?? null,
            'enabled' => $request->boolean('enabled'),
            'capi_enabled' => $request->boolean('capi_enabled'),
            'test_event_code' => $validated['test_event_code'] ?? null,
            'track_page_view' => $request->boolean('track_page_view'),
            'track_view_content' => $request->boolean('track_view_content'),
            'track_search' => $request->boolean('track_search'),
            'track_lead' => $request->boolean('track_lead'),
            'track_contact' => $request->boolean('track_contact'),
            'track_lead_started' => $request->boolean('track_lead_started'),
        ];

        if ($request->filled('capi_access_token')) {
            $updateData['capi_access_token'] = $request->input('capi_access_token');
        }

        $settings->update($updateData);

        return redirect()
            ->route('admin.meta-pixel-settings.edit')
            ->with('success', 'تم تحديث إعدادات Facebook Pixel بنجاح');
    }

    public function testCapi(Request $request, MetaPixelService $metaPixel)
    {
        $result = $metaPixel->sendTestEvent('Lead', $request);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
