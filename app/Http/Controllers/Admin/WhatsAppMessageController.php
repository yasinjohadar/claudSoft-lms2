<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\WhatsAppApiException;
use App\Http\Controllers\Controller;
use App\Jobs\BroadcastWhatsAppMessageJob;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppBroadcastRecipient;
use App\Models\WhatsAppContact;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppMessageTemplate;
use App\Services\WhatsApp\BroadcastWhatsAppMessage;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppProviderFactory;
use App\Services\WhatsApp\WhatsAppSettingsService;
use App\Support\UserPhoneCountryValidator;
use App\Support\WhatsAppRecipientNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class WhatsAppMessageController extends Controller
{
    public function __construct(
        private SendWhatsAppMessage $sendService,
        private BroadcastWhatsAppMessage $broadcastService,
        private WhatsAppSettingsService $settingsService
    ) {}

    /**
     * Return a user-friendly error message for WhatsApp errors (e.g. WhatsApp Web client errors).
     * When a known WhatsApp Web client error is detected, logs full details to storage/logs/whatsapp-*.log for diagnosis.
     */
    private function getWhatsAppErrorMessage(\Throwable $e, array $context = []): string
    {
        $msg = $e->getMessage();
        $isWhatsAppWebClientError = stripos($msg, 'markedUnread') !== false
            || stripos($msg, 'static.whatsapp.net') !== false;

        if ($isWhatsAppWebClientError) {
            Log::channel('whatsapp')->error('WhatsApp Web client error - full details for diagnosis', array_merge([
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
                'exception_code' => $e->getCode(),
            ], $context));

            return 'فشل إرسال الرسالة عبر واتساب ويب. جرّب إعادة ربط واتساب ويب أو المحاولة لاحقاً.';
        }

        if (stripos($msg, 'provided jid does not exist') !== false || stripos($msg, 'jid') !== false) {
            return 'فشل الإرسال: صيغة المستلم غير صحيحة أو الرقم/المجموعة غير موجودة على واتساب.';
        }

        return $msg;
    }

    private function toSingleLineError(string $message): string
    {
        $parts = preg_split('/\R+/', trim($message));

        return (string) ($parts[0] ?? 'حدث خطأ غير متوقع أثناء الإرسال.');
    }

    /**
     * Display messages list
     */
    public function index(Request $request)
    {
        $query = WhatsAppMessage::with('contact');

        // Filter by direction
        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('body', 'like', "%{$search}%")
                    ->orWhereHas('contact', function ($contactQuery) use ($search) {
                        $contactQuery->where('wa_id', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.pages.whatsapp-messages.index', compact('messages'));
    }

    /**
     * Display message details
     */
    public function show(WhatsAppMessage $message)
    {
        $message->load('contact');

        return view('admin.pages.whatsapp-messages.show', compact('message'));
    }

    /**
     * Display send message form
     */
    public function create()
    {
        $courses = Course::where('is_published', true)->orderBy('title')->get();
        $groups = CourseGroup::where('is_active', true)->orderBy('name')->get();
        $templates = WhatsAppMessageTemplate::active()->orderBy('name')->get(['id', 'name', 'body', 'type', 'language', 'meta_template_name']);
        $delaySettings = $this->settingsService->getDelaySettings();
        $whatsappSettings = $this->settingsService->getSettings();
        $queuePendingCount = (int) \Illuminate\Support\Facades\DB::table('jobs')->count();

        return view('admin.pages.whatsapp-messages.send', compact(
            'courses',
            'groups',
            'templates',
            'delaySettings',
            'whatsappSettings',
            'queuePendingCount'
        ));
    }

    /**
     * Search students for individual messaging
     */
    public function searchStudents(Request $request)
    {
        try {
            $query = User::query();

            // Filter students only (if student role exists)
            $hasStudentRole = \Spatie\Permission\Models\Role::where('name', 'student')->exists();
            if ($hasStudentRole) {
                try {
                    $query->students();
                } catch (\Exception $e) {
                    Log::warning('Error in students scope: '.$e->getMessage());
                }
            }

            // Filter by phone
            $query->whereNotNull('phone')
                ->where('phone', '!=', '');

            // Search
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');

                    if (is_numeric($search)) {
                        $q->orWhere('id', $search);
                    }
                });
            }

            $students = $query->limit(50)->get()->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email ?? '',
                    'phone' => $student->phone ?? '',
                ];
            });

            return response()->json($students);
        } catch (\Exception $e) {
            Log::error('Error searching students: '.$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send WhatsApp message
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'nullable|exists:users,id',
            'to' => 'required_without:student_id|string|max:255',
            'type' => 'required|in:text,template',
            'message' => 'required_if:type,text|nullable|string|max:4096',
            'template_name' => 'required_if:type,template|nullable|string|max:255',
            'language' => 'required_if:type,template|nullable|string|max:10',
        ], [
            'student_id.exists' => 'الطالب المحدد غير موجود',
            'to.required_without' => 'رقم الهاتف مطلوب إذا لم يتم اختيار طالب',
            'to.max' => 'حقل المستلم طويل جداً',
            'type.required' => 'نوع الرسالة مطلوب',
            'message.required_if' => 'نص الرسالة مطلوب',
            'template_name.required_if' => 'اسم القالب مطلوب',
            'language.required_if' => 'اللغة مطلوبة',
        ]);

        try {
            $phone = $validated['to'] ?? null;
            $student = null;
            $messageText = $validated['message'] ?? '';

            // If student_id is provided, get student and use their phone
            if (! empty($validated['student_id'])) {
                $student = User::findOrFail($validated['student_id']);
                if (! UserPhoneCountryValidator::isConsistent($student)) {
                    return redirect()->back()
                        ->with('error', 'رقم هاتف الطالب غير متطابق مع رمز الدولة أو غير صالح. يرجى تصحيحه من ملف الطالب.')
                        ->withInput();
                }
                $phone = $student->full_phone
                    ?? (trim(($student->country_code ?? '').($student->phone ?? '')))
                    ?: ($student->phone ?? '');
                if ($phone === '') {
                    return redirect()->back()
                        ->with('error', 'الطالب المحدد لا يملك رقم هاتف مسجل.')
                        ->withInput();
                }
                if (strpos($phone, '+') !== 0) {
                    $phone = '+'.ltrim($phone, '0');
                }

                // Replace placeholders if message is text type
                if ($validated['type'] === 'text' && ! empty($messageText)) {
                    $messageText = $this->broadcastService->replacePlaceholders(
                        $messageText,
                        $student,
                        null,
                        null
                    );
                }
            }

            // Get provider settings and send message directly (synchronous)
            $settings = $this->settingsService->getSettings();
            $provider = $settings['whatsapp_provider'] ?? 'meta';
            $config = $this->settingsService->getProviderConfig();
            $normalizedRecipient = WhatsAppRecipientNormalizer::normalize($provider, $phone);
            $contact = WhatsAppContact::findOrCreateByWaId($normalizedRecipient);

            // Create message record
            $message = WhatsAppMessage::create([
                'direction' => WhatsAppMessage::DIRECTION_OUTBOUND,
                'contact_id' => $contact->id,
                'type' => $validated['type'] === 'template' ? WhatsAppMessage::TYPE_TEMPLATE : WhatsAppMessage::TYPE_TEXT,
                'body' => $validated['type'] === 'template' ? $validated['template_name'] : $messageText,
                'status' => WhatsAppMessage::STATUS_QUEUED, // Will be updated after sending
                'payload' => $validated['type'] === 'template' ? [
                    'template_name' => $validated['template_name'],
                    'language' => $validated['language'] ?? 'ar',
                    'components' => [],
                ] : null,
            ]);

            // Create provider instance
            $providerInstance = WhatsAppProviderFactory::create($provider, $config);

            // Send message directly
            if ($validated['type'] === 'template') {
                $response = $providerInstance->sendTemplate(
                    $normalizedRecipient,
                    $validated['template_name'],
                    $validated['language'] ?? 'ar',
                    []
                );
            } else {
                $response = $providerInstance->sendText(
                    $normalizedRecipient,
                    $messageText,
                    false
                );
            }

            // Update message with meta_message_id and status
            $message->update([
                'meta_message_id' => $response->metaMessageId,
                'status' => WhatsAppMessage::STATUS_SENT,
                'payload' => array_merge($message->payload ?? [], [
                    'response' => $response->rawResponse,
                    'sent_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::channel('whatsapp')->info('WhatsApp message sent successfully (direct)', [
                'message_id' => $message->id,
                'meta_message_id' => $response->metaMessageId,
                'to' => $normalizedRecipient,
            ]);

            return redirect()->route('admin.whatsapp-messages.show', $message)
                ->with('success', 'تم إرسال الرسالة بنجاح!');
        } catch (WhatsAppApiException $e) {
            // Update message with error if message was created
            if (isset($message) && $message->id) {
                $message->update([
                    'status' => WhatsAppMessage::STATUS_FAILED,
                    'error' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'details' => $e->getDetails(),
                    ],
                ]);
            }

            Log::channel('whatsapp')->error('Failed to send WhatsApp message', [
                'message_id' => $message->id ?? null,
                'error' => $e->getMessage(),
                'details' => $e->getDetails(),
            ]);

            return redirect()->back()
                ->with('error', 'فشل إرسال الرسالة: '.$this->getWhatsAppErrorMessage($e))
                ->withInput();
        } catch (\Exception $e) {
            $safeMessage = $this->toSingleLineError($e->getMessage());

            // Update message with error if message was created
            if (isset($message) && $message->id) {
                $message->update([
                    'status' => WhatsAppMessage::STATUS_FAILED,
                    'error' => [
                        'message' => $safeMessage,
                    ],
                ]);
            }

            Log::channel('whatsapp')->error('Exception sending WhatsApp message', [
                'message_id' => $message->id ?? null,
                'error' => $safeMessage,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الرسالة: '.$this->getWhatsAppErrorMessage($e))
                ->withInput();
        } catch (\Throwable $e) {
            $safeMessage = $this->toSingleLineError($e->getMessage());

            if (isset($message) && ! empty($message->id)) {
                $message->update([
                    'status' => WhatsAppMessage::STATUS_FAILED,
                    'error' => [
                        'message' => $safeMessage,
                    ],
                ]);
            }
            Log::channel('whatsapp')->error('Throwable sending WhatsApp message', [
                'message_id' => isset($message) ? $message->id : null,
                'error' => $safeMessage,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الرسالة: '.$this->getWhatsAppErrorMessage($e))
                ->withInput();
        }
    }

    /**
     * Get students count by criteria (AJAX)
     */
    public function getStudentsCount(Request $request)
    {
        $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'group_id' => 'nullable|exists:course_groups,id',
        ]);

        $courseId = $request->filled('course_id') ? (int) $request->course_id : null;
        $groupId = $request->filled('group_id') ? (int) $request->group_id : null;

        Log::channel('whatsapp')->info('Broadcast students count requested', [
            'course_id' => $courseId,
            'group_id' => $groupId,
        ]);

        $students = $this->broadcastService->getStudentsByCriteria($courseId, $groupId);
        $count = $students->count();

        Log::channel('whatsapp')->info('Broadcast students count result', [
            'count' => $count,
            'course_id' => $courseId,
            'group_id' => $groupId,
        ]);

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Send broadcast message
     */
    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'send_type' => 'required|in:individual,broadcast',
            'type' => 'required|in:text,template',
            'message' => 'required_if:type,text|nullable|string|max:4096',
            'template_name' => 'required_if:type,template|nullable|string|max:255',
            'language' => 'required_if:type,template|nullable|string|max:10',
            // Broadcast: عند اختيار مجموعة يُرسل لأعضاء المجموعة فقط (الكورس اختياري)
            'course_id' => [
                Rule::requiredIf(fn () => $request->input('send_type') === 'broadcast' && ! $request->input('group_id')),
                'nullable',
                'exists:courses,id',
            ],
            'group_id' => 'nullable|exists:course_groups,id',
            // Individual field
            'to' => 'required_if:send_type,individual|nullable|string|max:255',
        ], [
            'send_type.required' => 'نوع الإرسال مطلوب',
            'type.required' => 'نوع الرسالة مطلوب',
            'message.required_if' => 'نص الرسالة مطلوب',
            'course_id.required' => 'اختر الكورس أو المجموعة للإرسال الجماعي',
            'course_id.required_if' => 'اختر الكورس أو المجموعة للإرسال الجماعي',
            'course_id.exists' => 'الكورس المحدد غير موجود',
            'group_id.exists' => 'المجموعة المحددة غير موجودة',
            'to.required_if' => 'رقم الهاتف مطلوب للإرسال الفردي',
        ]);

        try {
            if ($validated['send_type'] === 'individual') {
                // Redirect to regular send method
                return $this->send($request);
            }

            // Broadcast logic
            $students = $this->broadcastService->getStudentsByCriteria(
                $validated['course_id'] ?? null,
                $validated['group_id'] ?? null
            );

            if ($students->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'لا يوجد طلاب مطابقون للمعايير المحددة.')
                    ->withInput();
            }

            // Get course and group for placeholders
            $course = $validated['course_id'] ? Course::find($validated['course_id']) : null;
            $group = $validated['group_id'] ? CourseGroup::find($validated['group_id']) : null;

            // Create broadcast and recipient records first (so report can show per-recipient status)
            $broadcast = WhatsAppBroadcast::create([
                'message_template' => $validated['message'] ?? $validated['template_name'] ?? '',
                'send_type' => $validated['type'],
                'course_id' => $validated['course_id'] ?? null,
                'group_id' => $validated['group_id'] ?? null,
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

            // Send to first student synchronously so connection/API errors are shown to the user
            $firstStudent = $students->first();
            $firstMessage = $this->broadcastService->replacePlaceholders(
                $validated['message'] ?? $validated['template_name'] ?? '',
                $firstStudent,
                $course,
                $group
            );
            $phone = $firstStudent->full_phone ?? (($firstStudent->country_code ?? '').($firstStudent->phone ?? '')) ?: $firstStudent->phone;
            if (strpos($phone, '+') !== 0) {
                $phone = '+'.ltrim($phone, '0');
            }

            $settings = $this->settingsService->getSettings();
            $provider = $settings['whatsapp_provider'] ?? 'meta';
            $config = $this->settingsService->getProviderConfig();
            $providerInstance = WhatsAppProviderFactory::create($provider, $config);
            $normalizedRecipient = WhatsAppRecipientNormalizer::normalize($provider, $phone);

            try {
                if ($validated['type'] === 'template') {
                    $providerInstance->sendTemplate(
                        $normalizedRecipient,
                        $validated['template_name'],
                        $validated['language'] ?? 'ar',
                        []
                    );
                } else {
                    $providerInstance->sendText($normalizedRecipient, $firstMessage, false);
                }
            } catch (\Throwable $firstSendError) {
                $firstRecipient = WhatsAppBroadcastRecipient::where('broadcast_id', $broadcast->id)
                    ->where('user_id', $firstStudent->id)
                    ->first();
                if ($firstRecipient) {
                    $firstRecipient->update([
                        'status' => WhatsAppBroadcastRecipient::STATUS_FAILED,
                        'error_message' => $firstSendError->getMessage(),
                    ]);
                }
                $broadcast->increment('failed_count');
                Log::channel('whatsapp')->warning('First broadcast recipient failed; continuing with remaining recipients', [
                    'broadcast_id' => $broadcast->id,
                    'user_id' => $firstStudent->id,
                    'error' => $firstSendError->getMessage(),
                ]);
            }

            // If first send succeeded: update first recipient and broadcast counts
            if (! isset($firstSendError)) {
                $firstRecipient = WhatsAppBroadcastRecipient::where('broadcast_id', $broadcast->id)
                    ->where('user_id', $firstStudent->id)
                    ->first();
                if ($firstRecipient) {
                    $firstRecipient->update([
                        'status' => WhatsAppBroadcastRecipient::STATUS_SENT,
                        'sent_at' => now(),
                    ]);
                }
                $broadcast->increment('sent_count');
            }

            $delaySettings = $this->settingsService->getDelaySettings();
            $baseDelay = $delaySettings['delay_between_messages'];

            $index = 1;
            $cumulativeDelay = 0;
            foreach ($students->slice(1) as $student) {
                $message = $this->broadcastService->replacePlaceholders(
                    $validated['message'] ?? '',
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
                    $validated['type'],
                    $cumulativeDelay,
                    $index
                );
                $index++;
            }

            if ($students->count() === 1) {
                $broadcast->refresh();
                $status = $broadcast->sent_count > 0
                    ? WhatsAppBroadcast::STATUS_COMPLETED
                    : WhatsAppBroadcast::STATUS_FAILED;
                $broadcast->update(['status' => $status]);
            }

            return redirect()->route('admin.whatsapp-messages.broadcasts.show', $broadcast)
                ->with('success', 'تم بدء إرسال '.$students->count().' رسالة جماعية. سيتم تجاوز الأرقام غير الصالحة ومتابعة الإرسال. يمكنك متابعة التقرير في هذه الصفحة.');
        } catch (\Throwable $e) {
            Log::error('Error sending broadcast message: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'فشل إرسال الرسالة الجماعية: '.$this->getWhatsAppErrorMessage($e))
                ->withInput();
        }
    }

    /**
     * List past broadcasts (report index)
     */
    public function broadcastsIndex(Request $request)
    {
        $broadcasts = WhatsAppBroadcast::with(['course', 'group', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.pages.whatsapp-messages.broadcasts-index', compact('broadcasts'));
    }

    /**
     * Show broadcast report (detail)
     */
    public function showBroadcast(WhatsAppBroadcast $broadcast)
    {
        $broadcast->load(['course', 'group', 'creator', 'recipients.user']);

        return view('admin.pages.whatsapp-messages.broadcast-show', compact('broadcast'));
    }

    /**
     * Retry sending a failed or queued message (synchronous - without queue)
     */
    public function retry(WhatsAppMessage $message)
    {
        try {
            // Only allow retry for queued or failed messages
            if (! in_array($message->status, [WhatsAppMessage::STATUS_QUEUED, WhatsAppMessage::STATUS_FAILED])) {
                return redirect()->back()
                    ->with('error', 'لا يمكن إعادة إرسال هذه الرسالة. الحالة الحالية: '.$message->status);
            }

            // Load contact relationship
            $message->load('contact');
            if (! $message->contact) {
                return redirect()->back()
                    ->with('error', 'المستقبل غير موجود.');
            }

            $to = $message->contact->wa_id;

            // Get provider settings
            $settings = $this->settingsService->getSettings();
            $provider = $settings['whatsapp_provider'] ?? 'meta';
            $config = $this->settingsService->getProviderConfig();
            $normalizedRecipient = WhatsAppRecipientNormalizer::normalize($provider, $to);

            // Create provider instance
            $providerInstance = WhatsAppProviderFactory::create($provider, $config);

            // Send message directly (synchronous)
            if ($message->type === WhatsAppMessage::TYPE_TEMPLATE) {
                $payload = $message->payload ?? [];
                $response = $providerInstance->sendTemplate(
                    $normalizedRecipient,
                    $payload['template_name'] ?? $message->body,
                    $payload['language'] ?? 'ar',
                    $payload['components'] ?? []
                );
            } else {
                $response = $providerInstance->sendText(
                    $normalizedRecipient,
                    $message->body ?? '',
                    false
                );
            }

            // Update message with meta_message_id and status
            $message->update([
                'meta_message_id' => $response->metaMessageId,
                'status' => WhatsAppMessage::STATUS_SENT,
                'error' => null,
                'payload' => array_merge($message->payload ?? [], [
                    'response' => $response->rawResponse,
                    'sent_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::channel('whatsapp')->info('WhatsApp message sent successfully (retry)', [
                'message_id' => $message->id,
                'meta_message_id' => $response->metaMessageId,
                'to' => $normalizedRecipient,
            ]);

            return redirect()->back()
                ->with('success', 'تم إرسال الرسالة بنجاح!');
        } catch (WhatsAppApiException $e) {
            // Update message with error
            $message->update([
                'status' => WhatsAppMessage::STATUS_FAILED,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'details' => $e->getDetails(),
                    'retried_at' => now()->toIso8601String(),
                ],
            ]);

            Log::channel('whatsapp')->error('Failed to send WhatsApp message (retry)', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
                'details' => $e->getDetails(),
            ]);

            return redirect()->back()
                ->with('error', 'فشل إرسال الرسالة: '.$this->getWhatsAppErrorMessage($e));
        } catch (\Exception $e) {
            $safeMessage = $this->toSingleLineError($e->getMessage());

            // Update message with error
            $message->update([
                'status' => WhatsAppMessage::STATUS_FAILED,
                'error' => [
                    'message' => $safeMessage,
                    'retried_at' => now()->toIso8601String(),
                ],
            ]);

            Log::channel('whatsapp')->error('Exception sending WhatsApp message (retry)', [
                'message_id' => $message->id,
                'error' => $safeMessage,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الرسالة: '.$this->getWhatsAppErrorMessage($e));
        }
    }
}
