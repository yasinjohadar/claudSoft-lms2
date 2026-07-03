<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BroadcastTelegramMessageJob;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\TelegramBroadcast;
use App\Models\TelegramBroadcastRecipient;
use App\Models\TelegramMessageTemplate;
use App\Services\Telegram\BroadcastTelegramMessage;
use App\Services\Telegram\SendTelegramMessage;
use App\Services\Telegram\TelegramApiException;
use App\Services\Telegram\TelegramSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TelegramMessageController extends Controller
{
    public function __construct(
        private TelegramSettingsService $settingsService,
        private BroadcastTelegramMessage $broadcastService,
        private SendTelegramMessage $sendService,
    ) {}

    public function sendForm(): View
    {
        $templates = TelegramMessageTemplate::active()->orderBy('name')->get();

        return view('admin.pages.telegram.messages.send', compact('templates'));
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'chat_id' => 'required|string|max:100',
            'message' => 'required|string|max:5000',
        ]);

        try {
            $this->sendService->sendToChat($validated['chat_id'], $validated['message']);

            return back()->with('success', 'تم إرسال الرسالة.');
        } catch (\Throwable $e) {
            return back()->with('error', TelegramApiException::resolveUserMessage($e))->withInput();
        }
    }

    public function broadcastForm(): View
    {
        $courses = Course::orderBy('title')->get(['id', 'title']);
        $templates = TelegramMessageTemplate::active()->orderBy('name')->get();

        return view('admin.pages.telegram.messages.broadcast', compact('courses', 'templates'));
    }

    public function studentsCount(Request $request): JsonResponse
    {
        $courseId = $request->integer('course_id') ?: null;
        $groupId = $request->integer('group_id') ?: null;

        return response()->json([
            'count' => $this->broadcastService->countEligible($courseId, $groupId),
        ]);
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'course_id' => 'nullable|exists:courses,id',
            'group_id' => 'nullable|exists:course_groups,id',
            'telegram_template_id' => 'nullable|exists:telegram_message_templates,id',
        ]);

        $courseId = isset($validated['course_id']) ? (int) $validated['course_id'] : null;
        $groupId = isset($validated['group_id']) ? (int) $validated['group_id'] : null;

        $students = $this->broadcastService->getStudentsByCriteria($courseId, $groupId);
        if ($students->isEmpty()) {
            return back()->with('error', 'لا يوجد طلاب مربوطون بـ Telegram مطابقون للمعايير.')->withInput();
        }

        $course = $courseId ? Course::find($courseId) : null;
        $group = $groupId ? CourseGroup::find($groupId) : null;
        $templateBody = $validated['message'];

        if (! empty($validated['telegram_template_id'])) {
            $template = TelegramMessageTemplate::active()->findOrFail($validated['telegram_template_id']);
            $templateBody = $template->body;
        }

        $broadcast = TelegramBroadcast::create([
            'message_template' => $templateBody,
            'send_type' => 'text',
            'target_type' => TelegramBroadcast::TARGET_STUDENTS,
            'course_id' => $courseId,
            'group_id' => $groupId,
            'total_recipients' => $students->count(),
            'status' => TelegramBroadcast::STATUS_PROCESSING,
            'created_by' => Auth::id(),
        ]);

        foreach ($students as $student) {
            TelegramBroadcastRecipient::create([
                'broadcast_id' => $broadcast->id,
                'user_id' => $student->id,
                'status' => TelegramBroadcastRecipient::STATUS_PENDING,
            ]);
        }

        $baseDelay = $this->settingsService->calculateDelay();
        $cumulativeDelay = 0;
        $index = 0;

        foreach ($students as $student) {
            $message = $this->broadcastService->replacePlaceholders($templateBody, $student, $course, $group);
            if ($index === 0) {
                try {
                    $this->sendService->sendToUser($student, $message);
                    TelegramBroadcastRecipient::where('broadcast_id', $broadcast->id)
                        ->where('user_id', $student->id)
                        ->update(['status' => TelegramBroadcastRecipient::STATUS_SENT, 'sent_at' => now()]);
                    $broadcast->increment('sent_count');
                } catch (\Throwable $e) {
                    TelegramBroadcastRecipient::where('broadcast_id', $broadcast->id)
                        ->where('user_id', $student->id)
                        ->update(['status' => TelegramBroadcastRecipient::STATUS_FAILED, 'error_message' => $e->getMessage()]);
                    $broadcast->increment('failed_count');
                }
            } else {
                $cumulativeDelay += $baseDelay;
                BroadcastTelegramMessageJob::dispatch($broadcast, $student, $message, $cumulativeDelay);
            }
            $index++;
        }

        if ($students->count() === 1) {
            $broadcast->refresh();
            $broadcast->update([
                'status' => $broadcast->sent_count > 0 ? TelegramBroadcast::STATUS_COMPLETED : TelegramBroadcast::STATUS_FAILED,
            ]);
        }

        return redirect()->route('admin.telegram.broadcasts.show', $broadcast)
            ->with('success', 'تم بدء بث Telegram لـ '.$students->count().' طالب.');
    }

    public function broadcastsIndex(): View
    {
        $broadcasts = TelegramBroadcast::with(['course', 'group', 'creator'])
            ->latest('id')
            ->paginate(20);

        return view('admin.pages.telegram.messages.broadcasts-index', compact('broadcasts'));
    }

    public function showBroadcast(TelegramBroadcast $broadcast): View
    {
        $broadcast->load(['recipients.user', 'course', 'group']);

        return view('admin.pages.telegram.messages.broadcast-show', compact('broadcast'));
    }
}
