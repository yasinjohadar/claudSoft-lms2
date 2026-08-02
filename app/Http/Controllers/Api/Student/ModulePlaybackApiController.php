<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Video;
use App\Services\Video\BunnyStreamPlaybackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * تشغيل فيديو موقّع للتطبيقات (ديسكتوب/موبايل).
 *
 * الويب يضمّن من claudsoft.com فيعمل مع Bunny Allowed Domains.
 * الديسكتوب يعمل من localhost فيُرفض حتى مع token صحيح — لذلك نوفر
 * صفحة HTML على نطاق الـ API تضم iframe Bunny فقط (بدون واجهة التعلّم).
 */
class ModulePlaybackApiController extends Controller
{
    public function __construct(
        protected BunnyStreamPlaybackService $playback
    ) {}

    public function show(Request $request, int $moduleId): Response|JsonResponse
    {
        $resolved = $this->resolvePlayback($request, $moduleId);

        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        // مشغّل HTML فقط — للتضمين من الديسكتوب عبر المسار المنشور أصلاً /playback
        if ($this->wantsHtmlPlayer($request)) {
            return $this->htmlPlayerResponse($resolved);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء رابط التشغيل',
            'data' => $resolved,
        ]);
    }

    /**
     * صفحة مشغّل HTML للتضمين من الديسكتوب/WebView (?token= مدعوم).
     */
    public function player(Request $request, int $moduleId): Response|JsonResponse
    {
        $resolved = $this->resolvePlayback($request, $moduleId);

        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        return $this->htmlPlayerResponse($resolved);
    }

    private function wantsHtmlPlayer(Request $request): bool
    {
        $format = strtolower((string) $request->query('format', ''));
        if (in_array($format, ['html', 'player', 'embed'], true)) {
            return true;
        }

        return $request->boolean('html')
            || $request->prefers(['text/html', 'application/json']) === 'text/html';
    }

    /**
     * @param  array<string, mixed>  $resolved
     */
    private function htmlPlayerResponse(array $resolved): Response
    {
        $embedUrl = (string) $resolved['embed_url'];
        $title = e((string) ($resolved['title'] ?? 'فيديو'));
        $safeEmbed = e($embedUrl);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>{$title}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { width: 100%; height: 100%; background: #000; overflow: hidden; }
    iframe {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      border: 0;
      display: block;
    }
  </style>
</head>
<body>
  <iframe
    src="{$safeEmbed}"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
    allowfullscreen
    referrerpolicy="strict-origin-when-cross-origin"
  ></iframe>
</body>
</html>
HTML;

        $response = response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Security-Policy' => 'frame-ancestors *',
        ]);

        $response->headers->remove('X-Frame-Options');

        return $response;
    }

    /**
     * @return array<string, mixed>|JsonResponse
     */
    private function resolvePlayback(Request $request, int $moduleId): array|JsonResponse
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
        $title = $module->title !== null
            ? (string) $module->title
            : ($modulable->title !== null ? (string) $modulable->title : null);

        return [
            'module_id' => (int) $module->id,
            'title' => $title,
            'course_id' => (int) $module->course_id,
            'course_title' => $module->course?->title,
            'embed_url' => $embedUrl,
            'video_url' => $embedUrl,
            'player_path' => '/student/modules/'.$module->id.'/player',
            'expires_at' => $expires,
            'is_bunny' => $isBunny,
            'token_auth_enabled' => $tokenAuthEnabled,
            'duration' => isset($modulable->duration) ? (int) $modulable->duration : null,
            'thumbnail' => isset($modulable->thumbnail) ? (string) $modulable->thumbnail : null,
        ];
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
