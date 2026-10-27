<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileAppRelease;
use App\Services\MobileApp\MobileAppReleaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * إدارة تحديثات تطبيق الجوال: الإصدارات، الإجباري/الاختياري، وإشعار الطلاب.
 */
class MobileAppReleaseController extends Controller
{
    public function __construct(protected MobileAppReleaseService $releases) {}

    public function index(Request $request)
    {
        $platform = $request->query('platform');

        $releases = MobileAppRelease::query()
            ->when(array_key_exists((string) $platform, MobileAppRelease::PLATFORMS), fn ($q) => $q->forPlatform($platform))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // أحدث إصدار نشط لكل منصة (بطاقات أعلى الصفحة).
        $latest = collect(array_keys(MobileAppRelease::PLATFORMS))->mapWithKeys(fn ($p) => [
            $p => MobileAppRelease::active()->forPlatform($p)->orderByDesc('build_number')->first(),
        ]);

        return view('admin.pages.mobile-app-releases.index', compact('releases', 'latest', 'platform'));
    }

    public function create()
    {
        $release = new MobileAppRelease([
            'platform' => 'android',
            'distribution' => 'store',
            'is_active' => true,
            'is_forced' => false,
        ]);

        // اقتراح رقم البناء التالي.
        $nextBuild = (int) MobileAppRelease::forPlatform('android')->max('build_number') + 1;

        return view('admin.pages.mobile-app-releases.create', compact('release', 'nextBuild'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        $release = MobileAppRelease::create($data);

        if ($request->boolean('notify_now')) {
            $sent = $this->releases->notifyStudents($release, $request->boolean('app_users_only', true));

            return redirect()->route('admin.mobile-app-releases.index')
                ->with('success', "تم نشر الإصدار {$release->version_name} وإرسال الإشعار إلى {$sent} طالب");
        }

        return redirect()->route('admin.mobile-app-releases.index')
            ->with('success', "تم نشر الإصدار {$release->version_name} بنجاح");
    }

    public function edit(MobileAppRelease $mobileAppRelease)
    {
        return view('admin.pages.mobile-app-releases.edit', ['release' => $mobileAppRelease, 'nextBuild' => null]);
    }

    public function update(Request $request, MobileAppRelease $mobileAppRelease)
    {
        $mobileAppRelease->update($this->validated($request, $mobileAppRelease));

        return redirect()->route('admin.mobile-app-releases.index')
            ->with('success', 'تم تحديث بيانات الإصدار بنجاح');
    }

    public function destroy(Request $request, MobileAppRelease $mobileAppRelease)
    {
        $mobileAppRelease->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'تم حذف الإصدار بنجاح']);
        }

        return redirect()->route('admin.mobile-app-releases.index')->with('success', 'تم حذف الإصدار بنجاح');
    }

    public function toggleActive(MobileAppRelease $mobileAppRelease)
    {
        try {
            $mobileAppRelease->update(['is_active' => ! $mobileAppRelease->is_active]);

            return response()->json([
                'success' => true,
                'is_active' => $mobileAppRelease->is_active,
                'message' => $mobileAppRelease->is_active ? 'تم تفعيل الإصدار' : 'تم إيقاف الإصدار',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تحديث الحالة'], 500);
        }
    }

    public function notify(Request $request, MobileAppRelease $mobileAppRelease)
    {
        if (! $mobileAppRelease->is_active) {
            return response()->json(['success' => false, 'message' => 'فعّل الإصدار أولاً قبل إرسال الإشعار'], 422);
        }

        try {
            $sent = $this->releases->notifyStudents($mobileAppRelease, $request->boolean('app_users_only', true));

            return response()->json([
                'success' => true,
                'sent' => $sent,
                'message' => "تم إرسال إشعار التحديث إلى {$sent} طالب",
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to notify students about mobile app release', [
                'release_id' => $mobileAppRelease->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'تعذّر إرسال الإشعار، حاول مرة أخرى'], 500);
        }
    }

    protected function validated(Request $request, ?MobileAppRelease $release = null): array
    {
        $data = $request->validate([
            'platform' => ['required', Rule::in(array_keys(MobileAppRelease::PLATFORMS))],
            'version_name' => ['required', 'string', 'max:30', 'regex:/^\d+(\.\d+){0,3}$/'],
            'build_number' => [
                'required', 'integer', 'min:1',
                Rule::unique('mobile_app_releases')
                    ->where(fn ($q) => $q->where('platform', $request->input('platform')))
                    ->ignore($release?->id),
            ],
            'distribution' => ['required', Rule::in(array_keys(MobileAppRelease::DISTRIBUTIONS))],
            'download_url' => ['required', 'url', 'max:1000'],
            'release_notes' => ['nullable', 'string', 'max:3000'],
            'is_forced' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'version_name.regex' => 'رقم الإصدار يجب أن يكون بصيغة مثل 1.2.0',
            'build_number.unique' => 'رقم البناء مستخدم مسبقاً لهذه المنصة',
            'download_url.url' => 'رابط التحميل غير صالح',
        ], [
            'platform' => 'المنصة',
            'version_name' => 'رقم الإصدار',
            'build_number' => 'رقم البناء',
            'distribution' => 'طريقة التوزيع',
            'download_url' => 'رابط التحميل',
            'release_notes' => 'ملاحظات الإصدار',
        ]);

        $data['is_forced'] = $request->boolean('is_forced');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
