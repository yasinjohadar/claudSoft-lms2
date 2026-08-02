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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * تشغيل فيديو موقّع للتطبيقات (ديسكتوب/موبايل).
 *
 * الـ iframe لا يُرسل Bearer؛ و?token= Sanctum يفشل غالباً داخل iframe على نفس النطاق.
 * لذلك نُصدر تذكرة قصيرة العمر (Cache) وصفحة HTML عامة تتحقق منها فقط.
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

        if ($this->wantsHtmlPlayer($request)) {
            return $this->htmlPlayerResponse($resolved);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء رابط التشغيل',
            'data' => $resolved,
        ]);
    }

    public function player(Request $request, int $moduleId): Response|JsonResponse
    {
        $resolved = $this->resolvePlayback($request, $moduleId);

        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        return $this->htmlPlayerResponse($resolved);
    }

    /**
     * مشغّل HTML عبر تذكرة قصيرة — بدون Sanctum (مناسب لـ iframe الديسكتوب).
     */
    public function playerFrame(Request $request, int $moduleId): Response|JsonResponse
    {
        $ticket = $request->query('ticket');
        if (! is_string($ticket) || $ticket === '') {
            return response()->json([
                'success' => false,
                'message' => 'تذكرة التشغيل مفقودة',
                'data' => null,
            ], 401);
        }

        $ticket = preg_replace('/[^a-zA-Z0-9]/', '', $ticket) ?? '';
        if (strlen($ticket) < 32) {
            return response()->json([
                'success' => false,
                'message' => 'تذكرة التشغيل غير صالحة',
                'data' => null,
            ], 401);
        }

        $payload = Cache::get($this->ticketCacheKey($ticket));
        if (! is_array($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'انتهت تذكرة التشغيل أو غير صالحة. أعد فتح الدرس.',
                'data' => null,
            ], 401);
        }

        if ((int) ($payload['module_id'] ?? 0) !== $moduleId) {
            return response()->json([
                'success' => false,
                'message' => 'تذكرة التشغيل غير متطابقة',
                'data' => null,
            ], 403);
        }

        $embedUrl = $payload['embed_url'] ?? null;
        if (! is_string($embedUrl) || $embedUrl === '') {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد رابط تشغيل',
                'data' => null,
            ], 404);
        }

        return $this->htmlPlayerResponse([
            'embed_url' => $embedUrl,
            'title' => $payload['title'] ?? 'فيديو',
        ]);
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

        $ttl = max(60, (int) config('services.bunny_stream.embed_token_ttl', 7200));
        $ticket = Str::random(64);
        Cache::put($this->ticketCacheKey($ticket), [
            'user_id' => (int) $user->id,
            'module_id' => (int) $module->id,
            'embed_url' => $embedUrl,
            'title' => $title,
        ], now()->addSeconds($ttl));

        $playerUrl = url('/api/student/modules/'.$module->id.'/player-frame?ticket='.$ticket);

        return [
            'module_id' => (int) $module->id,
            'title' => $title,
            'course_id' => (int) $module->course_id,
            'course_title' => $module->course?->title,
            'embed_url' => $embedUrl,
            'video_url' => $embedUrl,
            'player_url' => $playerUrl,
            'player_path' => '/student/modules/'.$module->id.'/player-frame?ticket='.$ticket,
            'ticket' => $ticket,
            'expires_at' => $expires,
            'is_bunny' => $isBunny,
            'token_auth_enabled' => $tokenAuthEnabled,
            'duration' => isset($modulable->duration) ? (int) $modulable->duration : null,
            'thumbnail' => isset($modulable->thumbnail) ? (string) $modulable->thumbnail : null,
        ];
    }

    private function ticketCacheKey(string $ticket): string
    {
        return 'student_video_player_ticket:'.$ticket;
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
