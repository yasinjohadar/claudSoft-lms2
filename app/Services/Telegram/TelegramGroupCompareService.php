<?php

namespace App\Services\Telegram;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use Illuminate\Support\Collection;

class TelegramGroupCompareService
{
    public function __construct(
        private TelegramBridgeClient $bridgeClient,
    ) {}

    public function isAvailable(): bool
    {
        return $this->bridgeClient->isConfigured();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listTelegramGroups(?int $accountId = null): array
    {
        if (! $this->isAvailable()) {
            return [];
        }

        $response = $this->bridgeClient->listGroups($accountId);

        return is_array($response['groups'] ?? null) ? $response['groups'] : (is_array($response) ? $response : []);
    }

    /**
     * @return array{members: array, username_index: array<string, true>, chat_id: string}
     */
    public function loadTelegramGroup(string $chatId, ?int $accountId = null): array
    {
        $membersRaw = $this->bridgeClient->getGroupMembers($chatId, $accountId);
        $members = is_array($membersRaw['members'] ?? null) ? $membersRaw['members'] : [];

        $usernameIndex = [];
        foreach ($members as $member) {
            $username = strtolower(ltrim((string) ($member['username'] ?? ''), '@'));
            if ($username !== '') {
                $usernameIndex[$username] = true;
            }
        }

        return [
            'members' => $members,
            'username_index' => $usernameIndex,
            'chat_id' => $chatId,
        ];
    }

    /**
     * @return Collection<int, User>
     */
    public function getPlatformStudents(?int $courseId, ?int $groupId): Collection
    {
        if ($groupId) {
            return User::query()
                ->whereHas('courseGroupMemberships', fn ($q) => $q->where('group_id', $groupId))
                ->orderBy('name')
                ->get();
        }

        if ($courseId) {
            return User::query()
                ->whereHas('courseEnrollments', fn ($q) => $q->where('course_id', $courseId)->where('enrollment_status', 'active'))
                ->orderBy('name')
                ->get();
        }

        return collect();
    }

    /**
     * @return array{in_telegram_not_platform: array, in_platform_not_telegram: array, matched: array}
     */
    public function compare(string $chatId, ?int $courseId, ?int $groupId, ?int $accountId = null): array
    {
        $tgData = $this->loadTelegramGroup($chatId, $accountId);
        $students = $this->getPlatformStudents($courseId, $groupId);

        $inPlatformNotTelegram = [];
        $matched = [];

        foreach ($students as $student) {
            $username = strtolower(ltrim((string) ($student->telegram_username ?? ''), '@'));
            if ($username !== '' && isset($tgData['username_index'][$username])) {
                $matched[] = [
                    'user_id' => $student->id,
                    'name' => $student->name,
                    'telegram_username' => $student->telegram_username,
                ];
            } else {
                $inPlatformNotTelegram[] = [
                    'user_id' => $student->id,
                    'name' => $student->name,
                    'telegram_chat_id' => $student->telegram_chat_id,
                    'telegram_username' => $student->telegram_username,
                    'has_telegram_link' => ! empty($student->telegram_chat_id),
                ];
            }
        }

        $platformUsernames = $students->pluck('telegram_username')
            ->filter()
            ->map(fn ($u) => strtolower(ltrim((string) $u, '@')))
            ->flip();

        $inTelegramNotPlatform = [];
        foreach ($tgData['members'] as $member) {
            $username = strtolower(ltrim((string) ($member['username'] ?? ''), '@'));
            if ($username === '' || isset($platformUsernames[$username])) {
                continue;
            }
            $inTelegramNotPlatform[] = $member;
        }

        return [
            'in_telegram_not_platform' => $inTelegramNotPlatform,
            'in_platform_not_telegram' => $inPlatformNotTelegram,
            'matched' => $matched,
        ];
    }

    public function autoCreateGroupForCourseGroup(CourseGroup $group, ?int $accountId = null): array
    {
        $title = $group->name ?? ('Group #'.$group->id);

        return $this->bridgeClient->createGroup($title, $accountId);
    }
}
