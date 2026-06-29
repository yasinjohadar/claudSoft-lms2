<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trusted_devices_only_enabled' => 'nullable|boolean',
            'auto_trust_first_device' => 'nullable|boolean',
        ]);

        $this->settingsService->update([
            'trusted_devices_only_enabled' => $request->boolean('trusted_devices_only_enabled'),
            'auto_trust_first_device' => $request->boolean('auto_trust_first_device'),
        ]);

        return redirect()
            ->route('admin.user-devices.security-settings')
            ->with('success', 'تم تحديث إعدادات أمان الأجهزة بنجاح.');
    }
}
