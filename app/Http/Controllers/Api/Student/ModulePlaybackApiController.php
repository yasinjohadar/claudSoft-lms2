<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Video;
use App\Services\Video\BunnyStreamPlaybackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * رابط تشغيل موقّع لوحدة فيديو — للتطبيقات (ديسكتوب/موبايل).
 * نفس آلية توقيع الويب عبر BunnyStreamPlaybackService دون تعديل واجهة الويب.
 */
class ModulePlaybackApiController extends Controller
{
    public function __construct(
        protected BunnyStreamPlaybackService $playback
    ) {}

    public function show(Request $request, int $moduleId): JsonResponse
    {
        $user = $request->user();

        $module = CourseModule::query()
            ->with(['modulable', 'course'])
            ->find($moduleId);

        if (! $module) {
            return response()->json([
                'success' => false,
                'message' => 'الوحدة غير موجودة',
                'data' => null,
            ], 404);
        }

        $enrollment = CourseEnrollment::query()
            ->where('course_id', $module->course_id)
            ->where('student_id', $user->id)
            ->first();

        if (! $enrollment || ! $enrollment->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'أنت غير مسجل في هذا الكورس',
                'data' => null,
            ], 403);
        }

        $modulable = $module->modulable;
        if (! $modulable instanceof Video) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الوحدة ليست فيديو',
                'data' => null,
            ], 422);
        }

        $modulable->loadMissing('bunnyStreamLibrary');

        $embedUrl = $modulable->getEmbedUrl();
        $tokenAuthEnabled = $this->playback->isEmbedTokenAuthEnabled();
        $isBunny = $modulable->isBunnyStreamVideo();

        if ($isBunny && $tokenAuthEnabled) {
            if (! is_string($embedUrl) || $embedUrl === '' || ! $this->urlHasBunnyToken($embedUrl)) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعذّر إنشاء رابط تشغيل موقّع لهذا الفيديو',
                    'data' => null,
                ], 503);
            }
        }

        if (! is_string($embedUrl) || $embedUrl === '') {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد رابط تشغيل لهذا الفيديو',
                'data' => null,
            ], 404);
        }

        $expires = $this->extractExpires($embedUrl);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء رابط التشغيل',
            'data' => [
                'module_id' => (int) $module->id,
                'title' => $module->title !== null ? (string) $module->title : ($modulable->title !== null ? (string) $modulable->title : null),
                'course_id' => (int) $module->course_id,
                'course_title' => $module->course?->title,
                'embed_url' => $embedUrl,
                'video_url' => $embedUrl,
                'expires_at' => $expires,
                'is_bunny' => $isBunny,
                'token_auth_enabled' => $tokenAuthEnabled,
                'duration' => isset($modulable->duration) ? (int) $modulable->duration : null,
                'thumbnail' => isset($modulable->thumbnail) ? (string) $modulable->thumbnail : null,
            ],
        ]);
    }

    private function urlHasBunnyToken(string $url): bool
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if (! is_string($query) || $query === '') {
            return false;
        }

        parse_str($query, $params);

        return isset($params['token'], $params['expires'])
            && is_string($params['token'])
            && $params['token'] !== ''
            && is_numeric($params['expires']);
    }

    private function extractExpires(string $url): ?int
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if (! is_string($query) || $query === '') {
            return null;
        }

        parse_str($query, $params);

        return isset($params['expires']) && is_numeric($params['expires'])
            ? (int) $params['expires']
            : null;
    }
}
