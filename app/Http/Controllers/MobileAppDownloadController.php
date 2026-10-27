<?php

namespace App\Http\Controllers;

use App\Models\MobileAppRelease;
use Illuminate\Support\Facades\Storage;

/**
 * تنزيل تطبيق أندرويد (APK) خارج Google Play.
 *
 * - /app/download           رابط ثابت يشاركه الأدمن مع الطلاب: يعطي دائماً أحدث إصدار نشط.
 * - /app/download/{release} إصدار محدد (هذا ما يفتحه زر "تحديث الآن" داخل التطبيق).
 */
class MobileAppDownloadController extends Controller
{
    public function latest()
    {
        $release = MobileAppRelease::active()
            ->forPlatform('android')
            ->orderByDesc('build_number')
            ->first();

        abort_unless($release, 404, 'لا يوجد إصدار منشور من التطبيق حالياً');

        return $this->send($release);
    }

    public function release(MobileAppRelease $release)
    {
        abort_unless($release->is_active && $release->platform === 'android', 404);

        return $this->send($release);
    }

    protected function send(MobileAppRelease $release)
    {
        // إصدار بلا ملف مرفوع: رابطه خارجي (موقع آخر / المتجر).
        if (! $release->hasApk()) {
            return redirect()->away($release->download_url);
        }

        $disk = Storage::disk(MobileAppRelease::APK_DISK);
        abort_unless($disk->exists($release->apk_path), 404, 'ملف التطبيق غير موجود على الخادم');

        $release->increment('download_count');

        return $disk->download($release->apk_path, $release->apkDownloadName(), [
            // نوع الملف الذي يتعرّف عليه أندرويد كتطبيق قابل للتثبيت.
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
