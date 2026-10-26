<?php

namespace App\Http\Controllers\Api\Mobile\V1;

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
 * مشغّل فيديو محمي لتطبيق الجوال (منفصل عن مشغّل تطبيق الديسكتوب).
 *
 * - لا يُرسل رابط Bunny الموقّع إلى التطبيق أبداً؛ يحصل التطبيق على تذكرة معتمة فقط.
 * - التذكرة مرتبطة بالطالب والدرس، صالحة لدقائق قليلة ولعدد محدود من مرات الفتح.
 * - صفحة المشغّل تُضيف علامة مائية متحركة باسم الطالب، وتمنع الحفظ/النسخ والتخزين المؤقت،
 *   وتعطّل زر ملء الشاشة الخاص بـ Bunny لصالح ملء شاشة يُبقي العلامة المائية ظاهرة.
 * - Bunny نفسه يقيّد التشغيل بالدومين المسموح (Referer) ويوقّع الرابط بمدة قصيرة.
 */
class ModulePlaybackController extends BaseMobileController
{
    /** مدة صلاحية التذكرة لفتح المشغّل. */
    private const TICKET_TTL_SECONDS = 600;

    /** أقصى عدد مرات فتح لنفس التذكرة (إعادة تحميل الـ WebView / تدوير الشاشة). */
    private const TICKET_MAX_OPENS = 3;

    /** صلاحية رابط Bunny الموقّع داخل الصفحة (يكفي لبدء التشغيل؛ البث يستمر بعدها). */
    private const BUNNY_TOKEN_TTL_SECONDS = 900;

    public function __construct(
        protected BunnyStreamPlaybackService $playback,
    ) {}

    public function __invoke(Request $request, int $module): JsonResponse
    {
        $user = $request->user();
        $courseModule = CourseModule::with(['modulable', 'course'])->find($module);

        if (! $courseModule) {
            return $this->error(__('mobile.module_not_found'), 404);
        }

        $enrollment = CourseEnrollment::query()
            ->where('course_id', $courseModule->course_id)
            ->where('student_id', $user->id)
            ->first();

        if (! $enrollment || ! $enrollment->canAccessContent()) {
            return $this->error(__('mobile.course_not_found'), 403);
        }

        $video = $courseModule->modulable;
        if (! $video instanceof Video) {
            return $this->error(__('mobile.module_not_video'), 422);
        }

        $video->loadMissing('bunnyStreamLibrary');
        $embedUrl = $video->isBunnyStreamVideo()
            ? $this->playback->signEmbedUrlForVideo($video, self::BUNNY_TOKEN_TTL_SECONDS)
            : $video->getEmbedUrl();

        if (! is_string($embedUrl) || $embedUrl === '') {
            return $this->error(__('mobile.video_unavailable'), 503);
        }

        $ticket = Str::random(64);
        Cache::put($this->cacheKey($ticket), [
            'user_id' => (int) $user->id,
            'module_id' => (int) $courseModule->id,
            'embed_url' => $embedUrl,
            'watermark' => $this->watermarkFor($user),
            'opens' => 0,
        ], now()->addSeconds(self::TICKET_TTL_SECONDS));

        return $this->success([
            'module_id' => (int) $courseModule->id,
            'title' => $courseModule->title ?: $video->title,
            // صفحة المشغّل فقط — بدون رابط الفيديو.
            'player_url' => route('api.mobile.v1.modules.player', ['module' => $courseModule->id, 'ticket' => $ticket]),
            'expires_in' => self::TICKET_TTL_SECONDS,
        ]);
    }

    public function player(Request $request, int $module): Response
    {
        $ticket = preg_replace('/[^a-zA-Z0-9]/', '', (string) $request->query('ticket', '')) ?? '';
        $key = $this->cacheKey($ticket);
        $payload = strlen($ticket) === 64 ? Cache::get($key) : null;

        if (! is_array($payload) || (int) $payload['module_id'] !== $module) {
            return $this->playerError(__('mobile.player_ticket_invalid'));
        }

        if ($payload['opens'] >= self::TICKET_MAX_OPENS) {
            Cache::forget($key);

            return $this->playerError(__('mobile.player_ticket_invalid'));
        }

        $payload['opens']++;
        Cache::put($key, $payload, now()->addSeconds(self::TICKET_TTL_SECONDS));

        $response = response()->view('mobile.player', [
            'embedUrl' => $payload['embed_url'],
            'watermark' => $payload['watermark'],
        ]);

        return $this->protect($response);
    }

    private function playerError(string $message): Response
    {
        return $this->protect(response()->view('mobile.player-error', ['message' => $message], 403));
    }

    /**
     * رؤوس الحماية: بلا تخزين مؤقت أو فهرسة، ويُسمح بالتضمين داخل التطبيق فقط عبر iframe/WebView.
     */
    private function protect(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        // Bunny يتحقق من الدومين عبر Referer — نرسل الأصل فقط دون مسار التذكرة.
        $response->headers->set('Referrer-Policy', 'origin');
        $response->headers->set('Content-Security-Policy', 'frame-ancestors *');
        $response->headers->remove('X-Frame-Options');

        return $response;
    }

    private function watermarkFor($user): string
    {
        $name = $user->name_ar ?: $user->name;
        $id = $user->student_id ?: '#'.$user->id;

        return trim($name.' · '.$id);
    }

    private function cacheKey(string $ticket): string
    {
        return 'mobile_player_ticket:'.$ticket;
    }
}
