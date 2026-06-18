<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BulkEmail\BulkEmailSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BulkEmailSettingsController extends Controller
{
    public function __construct(
        private BulkEmailSettingsService $settingsService
    ) {}

    public function index(): View
    {
        $this->settingsService->initializeDefaults();

        $settings = $this->settingsService->getSettings();

        $kpis = [
            'today_sent' => $this->settingsService->getTodaySentCount(),
            'active_campaigns' => $this->settingsService->getActiveCampaignsCount(),
            'max_recipients' => $settings['max_recipients_per_campaign'],
            'daily_limit' => $settings['daily_send_limit'],
        ];

        return view('admin.pages.bulk-emails.settings', compact('settings', 'kpis'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'base_delay_seconds' => 'required|integer|min:0|max:300',
            'random_delay_enabled' => 'nullable',
            'min_jitter_seconds' => 'required|integer|min:0|max:60',
            'max_jitter_seconds' => 'required|integer|min:0|max:120|gte:min_jitter_seconds',
            'batch_size' => 'required|integer|min:1|max:1000',
            'batch_pause_seconds' => 'required|integer|min:0|max:600',
            'max_recipients_per_campaign' => 'required|integer|min:0|max:100000',
            'daily_send_limit' => 'required|integer|min:0|max:1000000',
        ], [
            'base_delay_seconds.required' => 'التأخير الأساسي مطلوب.',
            'base_delay_seconds.min' => 'التأخير الأساسي لا يمكن أن يكون سالباً.',
            'min_jitter_seconds.required' => 'الحد الأدنى للتباين مطلوب.',
            'max_jitter_seconds.required' => 'الحد الأقصى للتباين مطلوب.',
            'max_jitter_seconds.gte' => 'الحد الأقصى للتباين يجب أن يكون أكبر من أو يساوي الحد الأدنى.',
            'batch_size.required' => 'حجم الدفعة مطلوب.',
            'batch_size.min' => 'حجم الدفعة يجب أن يكون على الأقل 1.',
            'batch_pause_seconds.required' => 'مدة استراحة الدفعة مطلوبة.',
            'max_recipients_per_campaign.required' => 'حد المستلمين لكل حملة مطلوب.',
            'daily_send_limit.required' => 'الحد اليومي للإرسال مطلوب.',
        ]);

        try {
            $validated['random_delay_enabled'] = $request->has('random_delay_enabled');

            $this->settingsService->updateSettings($validated);

            return redirect()
                ->route('admin.bulk-emails.settings.index')
                ->with('success', 'تم حفظ إعدادات الإرسال الجماعي بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating bulk email settings: '.$e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حفظ الإعدادات: '.$e->getMessage())
                ->withInput();
        }
    }
}
