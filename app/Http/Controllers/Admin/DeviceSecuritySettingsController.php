<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Services\DeviceSecuritySettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceSecuritySettingsController extends Controller
{
    public function __construct(
        protected DeviceSecuritySettingsService $settingsService,
    ) {}

    public function edit(): View
    {
        return view('admin.user-devices.security-settings', [
            'settings' => $this->settingsService->all(),
            'courseGroups' => CourseGroup::query()
                ->withCount('members')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trusted_devices_only_enabled' => 'nullable|boolean',
            'auto_trust_first_device' => 'nullable|boolean',
            'single_session_enabled' => 'nullable|boolean',
            'bind_session_to_device_enabled' => 'nullable|boolean',
            'restricted_group_ids' => 'nullable|array',
            'restricted_group_ids.*' => 'integer|exists:course_groups,id',
        ]);

        $this->settingsService->update([
            'trusted_devices_only_enabled' => $request->boolean('trusted_devices_only_enabled'),
            'auto_trust_first_device' => $request->boolean('auto_trust_first_device'),
            'single_session_enabled' => $request->boolean('single_session_enabled'),
            'bind_session_to_device_enabled' => $request->boolean('bind_session_to_device_enabled'),
        ]);
        $this->settingsService->syncRestrictedGroups(
            $validated['restricted_group_ids'] ?? []
        );

        return redirect()
            ->route('admin.user-devices.security-settings')
            ->with('success', 'تم تحديث إعدادات أمان الأجهزة بنجاح.');
    }
}
