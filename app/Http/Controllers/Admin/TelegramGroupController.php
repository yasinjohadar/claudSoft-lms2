<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\TelegramBroadcast;
use App\Models\TelegramChannelLink;
use App\Services\Telegram\SendTelegramMessage;
use App\Services\Telegram\TelegramApiException;
use App\Services\Telegram\TelegramGroupCompareService;
use App\Services\Telegram\TelegramGroupLinkService;
use App\Services\Telegram\TelegramSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TelegramGroupController extends Controller
{
    public function __construct(
        private TelegramGroupLinkService $linkService,
        private TelegramGroupCompareService $compareService,
        private SendTelegramMessage $sendService,
        private TelegramSettingsService $settingsService,
    ) {}

    public function linkForm(Request $request): View
    {
        $courses = Course::orderBy('title')->get(['id', 'title']);
        $groups = CourseGroup::when($request->course_id, fn ($q) => $q->where('course_id', $request->course_id))
            ->orderBy('name')
            ->get(['id', 'name', 'course_id']);
        $settings = $this->settingsService->getSettings();
        $instructions = $this->linkService->linkInstructions($settings['bot_username'] ?? '');

        return view('admin.pages.telegram.groups.link', compact('courses', 'groups', 'instructions'));
    }

    public function linkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:course_groups,id',
            'telegram_chat_id' => 'required|string|max:64',
            'link_type' => 'required|in:group,channel',
            'title' => 'nullable|string|max:255',
            'invite_link' => 'nullable|url|max:500',
        ]);

        $group = CourseGroup::findOrFail($validated['group_id']);
        $inviteLink = $validated['invite_link']
            ?? $this->linkService->tryCreateInviteLink($validated['telegram_chat_id']);

        $this->linkService->linkToCourseGroup(
            $group,
            $validated['telegram_chat_id'],
            $validated['link_type'],
            $validated['title'] ?? $group->name,
            $inviteLink
        );

        return back()->with('success', 'تم ربط '.($validated['link_type'] === 'channel' ? 'القناة' : 'المجموعة').' بنجاح.');
    }

    public function prepareLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:course_groups,id',
            'telegram_chat_id' => 'nullable|string|max:64',
        ]);

        if (! empty($validated['telegram_chat_id'])) {
            cache()->put(
                'telegram_pending_link_group:'.$validated['telegram_chat_id'],
                (int) $validated['group_id'],
                now()->addMinutes(30)
            );
        }

        return back()->with('success', 'تم التحضير. أرسل /link_group داخل مجموعة Telegram (بعد إضافة البوت كمسؤول).');
    }

    public function postForm(): View
    {
        $links = TelegramChannelLink::where('is_active', true)->orderBy('title')->get();

        return view('admin.pages.telegram.groups.post', compact('links'));
    }

    public function post(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'telegram_chat_id' => 'required|string|max:64',
            'message' => 'required|string|max:5000',
            'title' => 'nullable|string|max:255',
        ]);

        try {
            $this->sendService->sendToChat($validated['telegram_chat_id'], $validated['message']);

            TelegramBroadcast::create([
                'message_template' => $validated['message'],
                'target_type' => TelegramBroadcast::TARGET_GROUP_CHAT,
                'telegram_chat_id' => $validated['telegram_chat_id'],
                'telegram_chat_title' => $validated['title'] ?? '',
                'total_recipients' => 1,
                'sent_count' => 1,
                'status' => TelegramBroadcast::STATUS_COMPLETED,
                'created_by' => Auth::id(),
            ]);

            return back()->with('success', 'تم النشر في Telegram.');
        } catch (\Throwable $e) {
            return back()->with('error', TelegramApiException::resolveUserMessage($e))->withInput();
        }
    }

    public function compareForm(): View
    {
        $courses = Course::orderBy('title')->get(['id', 'title']);
        $bridgeAvailable = $this->compareService->isAvailable();
        $telegramGroups = $bridgeAvailable ? $this->compareService->listTelegramGroups() : [];

        return view('admin.pages.telegram.groups.compare', compact('courses', 'bridgeAvailable', 'telegramGroups'));
    }

    public function compareRun(Request $request): View
    {
        $validated = $request->validate([
            'telegram_chat_id' => 'required|string|max:64',
            'course_id' => 'nullable|exists:courses,id',
            'group_id' => 'nullable|exists:course_groups,id',
        ]);

        $result = $this->compareService->compare(
            $validated['telegram_chat_id'],
            isset($validated['course_id']) ? (int) $validated['course_id'] : null,
            isset($validated['group_id']) ? (int) $validated['group_id'] : null,
        );

        $courses = Course::orderBy('title')->get(['id', 'title']);

        return view('admin.pages.telegram.groups.compare', [
            'courses' => $courses,
            'bridgeAvailable' => $this->compareService->isAvailable(),
            'telegramGroups' => $this->compareService->listTelegramGroups(),
            'compareResult' => $result,
            'selectedChatId' => $validated['telegram_chat_id'],
            'selectedCourseId' => $validated['course_id'] ?? null,
            'selectedGroupId' => $validated['group_id'] ?? null,
        ]);
    }

    public function autoCreate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:course_groups,id',
        ]);

        if (! $this->compareService->isAvailable()) {
            return back()->with('error', 'MTProto Bridge غير مُعد. أدخل عنوانه من إعدادات Telegram.');
        }

        try {
            $group = CourseGroup::findOrFail($validated['group_id']);
            $created = $this->compareService->autoCreateGroupForCourseGroup($group);
            $chatId = (string) ($created['chat_id'] ?? $created['id'] ?? '');
            $inviteLink = $created['invite_link'] ?? null;

            if ($chatId !== '') {
                $settings = $this->settingsService->getSettings();
                if (! empty($settings['bot_username'])) {
                    try {
                        app(\App\Services\Telegram\TelegramBridgeClient::class)
                            ->addBotToGroup($chatId, $settings['bot_username']);
                    } catch (\Throwable) {
                        // Bot add may fail if bridge doesn't support it yet
                    }
                }

                $this->linkService->linkToCourseGroup($group, $chatId, 'group', $group->name, $inviteLink);
            }

            return back()->with('success', 'تم إنشاء مجموعة Telegram وربطها.');
        } catch (\Throwable $e) {
            return back()->with('error', TelegramApiException::resolveUserMessage($e));
        }
    }
}
