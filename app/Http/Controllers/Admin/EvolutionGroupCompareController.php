<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BroadcastWhatsAppMessageJob;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppBroadcastRecipient;
use App\Services\WhatsApp\BroadcastWhatsAppMessage;
use App\Services\WhatsApp\Evolution\EvolutionGroupCompareService;
use App\Services\WhatsApp\Evolution\EvolutionService;
use App\Services\WhatsApp\WhatsAppProviderFactory;
use App\Services\WhatsApp\WhatsAppSettingsService;
use App\Support\WhatsAppRecipientNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EvolutionGroupCompareController extends Controller
{
    public function __construct(
        private EvolutionGroupCompareService $compareService,
        private EvolutionService $evolutionService,
        private BroadcastWhatsAppMessage $broadcastService,
        private WhatsAppSettingsService $settingsService,
    ) {}

    public function index(Request $request): View
    {
        $courses = Course::where('is_published', true)->orderBy('title')->get(['id', 'title']);
        $platformGroups = CourseGroup::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $delaySettings = $this->settingsService->getDelaySettings();
        $queuePendingCount = (int) \Illuminate\Support\Facades\DB::table('jobs')->count();

        $whatsappGroups = [];
        $waError = null;
        try {
            $whatsappGroups = $this->compareService->listWhatsAppGroups(false);
        } catch (\Throwable $e) {
            $waError = $e->getMessage();
        }

        $filters = $this->filtersFromRequest($request);
        $result = null;
        $waGroupInfo = null;
        $labels = null;
        $compareError = null;

        if ($filters['whatsapp_jid'] !== '') {
            try {
                $students = $this->compareService->getPlatformStudents(
                    $filters['course_id'],
                    $filters['platform_group_id'],
                    $filters['scope'],
                    $filters['active_only'],
                    false,
                );

                $wa = $this->compareService->loadWhatsAppGroup($filters['whatsapp_jid']);
                $waGroupInfo = $wa['group_info'];
                $result = $this->compareService->compare($students, $wa['phone_index'], $wa['members']);
                $labels = $this->compareService->resolveLabels($filters['course_id'], $filters['platform_group_id']);
            } catch (\Throwable $e) {
                $compareError = $e->getMessage();
            }
        }

        return view('admin.pages.evolution-api.groups.compare', compact(
            'courses',
            'platformGroups',
            'whatsappGroups',
            'waError',
            'filters',
            'result',
            'waGroupInfo',
            'labels',
            'compareError',
            'delaySettings',
            'queuePendingCount',
        ));
    }

    public function campaigns(Request $request): View
    {
        $campaigns = WhatsAppBroadcast::compareMissing()
            ->with(['course', 'group', 'creator'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.pages.evolution-api.groups.compare-campaigns', compact('campaigns'));
    }

    public function showCampaign(WhatsAppBroadcast $broadcast): View
    {
        abort_unless($broadcast->isCompareMissing(), 404);

        $broadcast->load(['course', 'group', 'creator', 'recipients.user']);

        $delaySettings = $this->settingsService->getDelaySettings();

        return view('admin.pages.evolution-api.groups.compare-campaign-show', compact('broadcast', 'delaySettings'));
    }

    public function export(Request $request): Response
    {
        $filters = $this->filtersFromRequest($request);
        abort_if($filters['whatsapp_jid'] === '', 422, 'يجب اختيار مجموعة واتساب.');

        $students = $this->compareService->getPlatformStudents(
            $filters['course_id'],
            $filters['platform_group_id'],
            $filters['scope'],
            $filters['active_only'],
            false,
        );
        $wa = $this->compareService->loadWhatsAppGroup($filters['whatsapp_jid']);
        $result = $this->compareService->compare($students, $wa['phone_index'], $wa['members']);

        $view = $filters['tab'] ?? 'missing';
        $rows = match ($view) {
            'matched' => $result['matched'],
            'wa_only' => $result['wa_only'],
            'no_phone' => $result['no_phone'],
            default => $result['missing'],
        };

        $filename = 'whatsapp-compare-' . $view . '-' . now()->format('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows, $view) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($view === 'wa_only') {
                fputcsv($out, ['الرقم', 'JID', 'الدور']);
                foreach ($rows as $row) {
                    fputcsv($out, [$row['phone'], $row['phone_jid'], $row['role'] ?? 'member']);
                }
            } else {
                fputcsv($out, ['ID', 'الاسم', 'البريد', 'الهاتف', 'المجموعات', 'الكورسات']);
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row['id'] ?? '',
                        $row['name'] ?? '',
                        $row['email'] ?? '',
                        $row['phone_display'] ?? $row['phone'] ?? '',
                        implode(' | ', $row['groups'] ?? []),
                        implode(' | ', $row['courses'] ?? []),
                    ]);
                }
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function messageMissing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'text' => ['required', 'string', 'max:5000'],
            'scope' => ['nullable', 'string'],
            'course_id' => ['nullable', 'integer'],
            'platform_group_id' => ['nullable', 'integer'],
            'whatsapp_jid' => ['nullable', 'string', 'max:255'],
            'whatsapp_group_name' => ['nullable', 'string', 'max:255'],
            'active_only' => ['nullable'],
        ]);

        $students = User::whereIn('id', $validated['user_ids'])->orderBy('name')->get();
        if ($students->isEmpty()) {
            return back()->with('error', 'لم يتم اختيار طلاب صالحين.');
        }

        $course = ! empty($validated['course_id']) ? Course::find($validated['course_id']) : null;
        $group = ! empty($validated['platform_group_id']) ? CourseGroup::find($validated['platform_group_id']) : null;

        $broadcast = WhatsAppBroadcast::create([
            'message_template' => $validated['text'],
            'send_type' => WhatsAppBroadcast::TYPE_TEXT,
            'campaign_type' => WhatsAppBroadcast::CAMPAIGN_COMPARE_MISSING,
            'course_id' => $validated['course_id'] ?? null,
            'group_id' => $validated['platform_group_id'] ?? null,
            'whatsapp_group_jid' => $validated['whatsapp_jid'] ?? null,
            'whatsapp_group_name' => $validated['whatsapp_group_name'] ?? null,
            'meta' => [
                'scope' => $validated['scope'] ?? 'group',
                'active_only' => $request->boolean('active_only', true),
                'source' => 'evolution_compare',
            ],
            'total_recipients' => $students->count(),
            'status' => WhatsAppBroadcast::STATUS_PROCESSING,
            'sent_count' => 0,
            'failed_count' => 0,
            'created_by' => Auth::id(),
        ]);

        foreach ($students as $student) {
            WhatsAppBroadcastRecipient::create([
                'broadcast_id' => $broadcast->id,
                'user_id' => $student->id,
                'status' => WhatsAppBroadcastRecipient::STATUS_PENDING,
            ]);
        }

        $firstStudent = $students->first();
        $firstMessage = $this->broadcastService->replacePlaceholders(
            $validated['text'],
            $firstStudent,
            $course,
            $group
        );

        $digits = $this->compareService->studentPhoneDigits($firstStudent);
        $firstSendError = null;

        if ($digits === null) {
            $firstSendError = new \RuntimeException('رقم الطالب الأول غير صالح.');
        } else {
            try {
                $settings = $this->settingsService->getSettings();
                $provider = $settings['whatsapp_provider'] ?? 'meta';
                $config = $this->settingsService->getProviderConfig();
                $providerInstance = WhatsAppProviderFactory::create($provider, $config);
                $to = WhatsAppRecipientNormalizer::normalize($provider, $digits);
                $providerInstance->sendText($to, $firstMessage, false);
            } catch (\Throwable $e) {
                $firstSendError = $e;
            }
        }

        if ($firstSendError) {
            WhatsAppBroadcastRecipient::where('broadcast_id', $broadcast->id)
                ->where('user_id', $firstStudent->id)
                ->update([
                    'status' => WhatsAppBroadcastRecipient::STATUS_FAILED,
                    'error_message' => $firstSendError->getMessage(),
                ]);
            $broadcast->increment('failed_count');
            Log::channel('whatsapp')->warning('Compare campaign first send failed', [
                'broadcast_id' => $broadcast->id,
                'user_id' => $firstStudent->id,
                'error' => $firstSendError->getMessage(),
            ]);
        } else {
            WhatsAppBroadcastRecipient::where('broadcast_id', $broadcast->id)
                ->where('user_id', $firstStudent->id)
                ->update([
                    'status' => WhatsAppBroadcastRecipient::STATUS_SENT,
                    'sent_at' => now(),
                ]);
            $broadcast->increment('sent_count');
        }

        $delaySettings = $this->settingsService->getDelaySettings();
        $baseDelay = $delaySettings['delay_between_messages'];
        $cumulativeDelay = 0;
        $index = 1;

        foreach ($students->slice(1) as $student) {
            $message = $this->broadcastService->replacePlaceholders(
                $validated['text'],
                $student,
                $course,
                $group
            );
            $delay = $this->settingsService->calculateDelay($baseDelay);
            $cumulativeDelay += $delay;

            BroadcastWhatsAppMessageJob::dispatch(
                $broadcast,
                $student,
                $message,
                WhatsAppBroadcast::TYPE_TEXT,
                $cumulativeDelay,
                $index
            );
            $index++;
        }

        if ($students->count() === 1) {
            $broadcast->refresh();
            $broadcast->update([
                'status' => $broadcast->sent_count > 0
                    ? WhatsAppBroadcast::STATUS_COMPLETED
                    : WhatsAppBroadcast::STATUS_FAILED,
            ]);
        }

        $delayInfo = $delaySettings['delay_between_messages'] . ' ث';
        if ($delaySettings['random_delay_enabled']) {
            $delayInfo .= ' (+ عشوائي ' . $delaySettings['min_delay'] . '–' . $delaySettings['max_delay'] . ' ث)';
        }

        return redirect()
            ->route('admin.evolution-api.groups.compare.campaigns.show', $broadcast)
            ->with('success', 'تم بدء إرسال ' . $students->count() . ' رسالة عبر الطابور. الفاصل بين الرسائل: ' . $delayInfo . '. تابع التقرير في هذه الصفحة.');
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'scope' => $request->input('scope', 'group'),
            'course_id' => $request->filled('course_id') ? (int) $request->course_id : null,
            'platform_group_id' => $request->filled('platform_group_id') ? (int) $request->platform_group_id : null,
            'whatsapp_jid' => (string) $request->input('whatsapp_jid', ''),
            'active_only' => $request->boolean('active_only', true),
            'tab' => $request->input('tab', 'missing'),
        ];
    }
}
