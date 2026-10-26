<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

/**
 * دروس المحتوى المكتوب (صفحات توثيق / دروس HTML) لتطبيق الجوال.
 *
 * WebView / iframe لا يرسل Bearer token، لذا:
 *  1) show(): بتوكن Sanctum، يتحقق من التسجيل ويعيد رابطاً موقّعاً مؤقتاً.
 *  2) view(): عام لكن محمي بالتوقيع، يعرض المحتوى بنفس قالب وتنسيق صفحات التوثيق العامة.
 */
class ModuleDocumentController extends BaseMobileController
{
    private const LINK_TTL_MINUTES = 120;

    public function show(Request $request, int $module): JsonResponse
    {
        $courseModule = CourseModule::with('modulable')->find($module);

        if (! $courseModule) {
            return $this->error(__('mobile.module_not_found'), 404);
        }

        if (! $this->canAccess($request->user(), $courseModule)) {
            return $this->error(__('mobile.course_not_found'), 403);
        }

        $content = $this->contentOf($courseModule);
        if ($content === null) {
            return $this->error(__('mobile.module_has_no_document'), 422);
        }

        $url = URL::temporarySignedRoute(
            'api.mobile.v1.modules.document.view',
            now()->addMinutes(self::LINK_TTL_MINUTES),
            ['module' => $courseModule->id, 'user' => $request->user()->id],
        );

        // نفس تقدير لوحة الطالب: ~180 كلمة عربية في الدقيقة.
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($content)) ?? '');
        $words = $plain === '' ? 0 : count(preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY));

        return $this->success([
            'module_id' => $courseModule->id,
            'title' => $courseModule->title ?: $courseModule->modulable?->title,
            'excerpt' => $courseModule->modulable?->excerpt ?? null,
            'reading_minutes' => $words > 0 ? max(1, (int) ceil($words / 180)) : null,
            // يُضاف ?theme=dark|light من التطبيق (خارج التوقيع).
            'document_url' => $url,
            'expires_at' => now()->addMinutes(self::LINK_TTL_MINUTES)->toIso8601String(),
        ]);
    }

    public function view(Request $request, int $module): Response
    {
        abort_unless($request->hasValidSignatureWhileIgnoring(['theme']), 403, __('mobile.document_link_expired'));

        $courseModule = CourseModule::with(['modulable', 'section'])->findOrFail($module);
        $user = User::findOrFail((int) $request->query('user'));

        abort_unless($this->canAccess($user, $courseModule), 403);

        $content = $this->contentOf($courseModule);
        abort_if($content === null, 404);

        $response = response()->view('mobile.document', [
            'title' => $courseModule->title ?: $courseModule->modulable?->title,
            'sectionTitle' => $courseModule->section?->title,
            'content' => $content,
            'forcedTheme' => $request->query('theme') === 'light' ? 'light' : 'dark',
        ]);

        // يُعرض داخل iframe (تطبيق الويب) أو WebView.
        $response->headers->set('Content-Security-Policy', 'frame-ancestors *');
        $response->headers->remove('X-Frame-Options');

        return $response;
    }

    private function canAccess(User $user, CourseModule $module): bool
    {
        $enrollment = CourseEnrollment::query()
            ->where('course_id', $module->course_id)
            ->where('student_id', $user->id)
            ->first();

        return $enrollment !== null && $enrollment->canAccessContent();
    }

    /**
     * محتوى HTML للدرس: صفحة توثيق أو درس نصي.
     */
    private function contentOf(CourseModule $module): ?string
    {
        $content = $module->modulable?->content ?? null;

        return is_string($content) && trim($content) !== '' ? $content : null;
    }
}
