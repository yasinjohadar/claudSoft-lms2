<?php

namespace App\Services\WhatsApp\Evolution;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use App\Services\WhatsApp\BroadcastWhatsAppMessage;
use App\Support\EvolutionGroupMemberParser;
use App\Support\UserPhoneCountryValidator;
use App\Support\WapiPhoneNormalizer;
use Illuminate\Support\Collection;

class EvolutionGroupCompareService
{
    public function __construct(
        private EvolutionService $evolutionService
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listWhatsAppGroups(bool $withParticipants = false): array
    {
        $instance = $this->evolutionService->activeInstanceName();
        if ($instance === '') {
            return [];
        }

        $response = $this->evolutionService->clientFor(null, $instance)->fetchAllGroups($instance, $withParticipants);

        return is_array($response) ? $response : [];
    }

    /**
     * @return array{members: array, phone_index: array<string, true>, group_info: array}
     */
    public function loadWhatsAppGroup(string $groupJid): array
    {
        $instance = $this->evolutionService->activeInstanceName();
        if ($instance === '') {
            throw new \RuntimeException('لم يُحدَّد Instance افتراضي لـ Evolution API. عيّن الانستانس من صفحة Evolution API.');
        }

        $client = $this->evolutionService->clientFor(null, $instance);
        $group = $client->findGroupByJid($instance, $groupJid);
        $membersRaw = $client->findGroupMembers($instance, $groupJid);
        $members = EvolutionGroupMemberParser::parse($membersRaw);
        $groupInfo = EvolutionGroupMemberParser::summarizeGroup($group, $groupJid);

        return [
            'members' => $members,
            'phone_index' => $this->buildPhoneIndex($members),
            'group_info' => $groupInfo,
        ];
    }

    /**
     * @return Collection<int, User>
     */
    public function getPlatformStudents(
        ?int $courseId,
        ?int $platformGroupId,
        string $scope = 'group',
        bool $activeEnrollmentOnly = true,
        bool $requireValidPhone = false,
    ): Collection {
        $scope = in_array($scope, ['group', 'course', 'both', 'either'], true) ? $scope : 'group';

        if ($scope === 'group' && ! $platformGroupId) {
            return collect();
        }
        if ($scope === 'course' && ! $courseId) {
            return collect();
        }
        if ($scope === 'both' && (! $platformGroupId || ! $courseId)) {
            return collect();
        }
        if ($scope === 'either' && ! $platformGroupId && ! $courseId) {
            return collect();
        }

        $query = User::query()->with(['courseGroupMemberships.group', 'courseEnrollments.course']);

        if ($scope === 'either') {
            $query->where(function ($q) use ($platformGroupId, $courseId, $activeEnrollmentOnly) {
                if ($platformGroupId) {
                    $q->whereHas('courseGroupMemberships', fn ($m) => $m->where('group_id', $platformGroupId));
                }
                if ($courseId) {
                    $enrollmentQuery = function ($e) use ($courseId, $activeEnrollmentOnly) {
                        $e->where('course_id', $courseId);
                        if ($activeEnrollmentOnly) {
                            $e->where('enrollment_status', 'active');
                        }
                    };
                    if ($platformGroupId) {
                        $q->orWhereHas('courseEnrollments', $enrollmentQuery);
                    } else {
                        $q->whereHas('courseEnrollments', $enrollmentQuery);
                    }
                }
            });
        } elseif ($scope === 'both') {
            $query->whereHas('courseGroupMemberships', fn ($q) => $q->where('group_id', $platformGroupId))
                ->whereHas('courseEnrollments', function ($q) use ($courseId, $activeEnrollmentOnly) {
                    $q->where('course_id', $courseId);
                    if ($activeEnrollmentOnly) {
                        $q->where('enrollment_status', 'active');
                    }
                });
        } elseif ($scope === 'group') {
            $query->whereHas('courseGroupMemberships', fn ($q) => $q->where('group_id', $platformGroupId));
        } elseif ($scope === 'course') {
            $query->whereHas('courseEnrollments', function ($q) use ($courseId, $activeEnrollmentOnly) {
                $q->where('course_id', $courseId);
                if ($activeEnrollmentOnly) {
                    $q->where('enrollment_status', 'active');
                }
            });
        }

        $students = $query->orderBy('name')->get();

        if ($requireValidPhone) {
            return $students->filter(fn (User $u) => $this->studentPhoneDigits($u) !== null)->values();
        }

        return $students;
    }

    /**
     * @param  array<string, true>  $phoneIndex
     * @return array{
     *   stats: array<string, int>,
     *   missing: array<int, array<string, mixed>>,
     *   matched: array<int, array<string, mixed>>,
     *   wa_only: array<int, array<string, mixed>>,
     *   no_phone: array<int, array<string, mixed>>
     * }
     */
    public function compare(Collection $students, array $phoneIndex, array $waMembers): array
    {
        $missing = [];
        $matched = [];
        $noPhone = [];
        $matchedWaKeys = [];

        foreach ($students as $student) {
            $digits = $this->studentPhoneDigits($student);
            $row = $this->studentRow($student, $digits);

            if ($digits === null) {
                $noPhone[] = $row;

                continue;
            }

            if ($this->isInWhatsAppGroup($digits, $phoneIndex)) {
                $matched[] = $row;
                foreach ($this->phoneMatchKeys($digits) as $key) {
                    $matchedWaKeys[$key] = true;
                }
            } else {
                $missing[] = $row;
            }
        }

        $waOnly = [];
        foreach ($waMembers as $member) {
            $digits = WapiPhoneNormalizer::normalize($member['phone'] ?? '');
            if ($digits === '') {
                continue;
            }
            $keys = $this->phoneMatchKeys($digits);
            $linkedToPlatform = false;
            foreach ($keys as $key) {
                if (isset($matchedWaKeys[$key])) {
                    $linkedToPlatform = true;
                    break;
                }
            }
            if (! $linkedToPlatform) {
                $waOnly[] = [
                    'phone' => $member['phone'],
                    'phone_jid' => $member['phone_jid'] ?? '',
                    'is_admin' => $member['is_admin'] ?? false,
                    'role' => $member['role'] ?? 'member',
                ];
            }
        }

        return [
            'stats' => [
                'platform_total' => $students->count(),
                'wa_total' => count($waMembers),
                'matched' => count($matched),
                'missing' => count($missing),
                'wa_only' => count($waOnly),
                'no_phone' => count($noPhone),
            ],
            'missing' => $missing,
            'matched' => $matched,
            'wa_only' => $waOnly,
            'no_phone' => $noPhone,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $members
     * @return array<string, true>
     */
    public function buildPhoneIndex(array $members): array
    {
        $index = [];
        foreach ($members as $member) {
            $digits = WapiPhoneNormalizer::normalize($member['phone'] ?? EvolutionGroupMemberParser::extractPhone($member['phone_jid'] ?? ''));
            if ($digits === '') {
                continue;
            }
            foreach ($this->phoneMatchKeys($digits) as $key) {
                $index[$key] = true;
            }
        }

        return $index;
    }

    public function studentPhoneDigits(User $user): ?string
    {
        if (! UserPhoneCountryValidator::isConsistent($user)) {
            return null;
        }

        $phone = $user->full_phone
            ?? trim(($user->country_code ?? '') . ($user->phone ?? ''))
            ?: ($user->phone ?? '');

        $phone = preg_replace('/\s+/', '', $phone ?? '');
        if ($phone !== '' && ! str_starts_with($phone, '+')) {
            $phone = '+' . ltrim($phone, '0');
        }

        $digits = WapiPhoneNormalizer::normalize($phone);

        return WapiPhoneNormalizer::isValidE164Digits($digits) ? $digits : null;
    }

    /**
     * @param  iterable<User>  $users
     * @return array<int, 'in_group'|'not_in_group'|'no_phone'>
     */
    public function waMembershipStatusForUsers(iterable $users, array $phoneIndex, BroadcastWhatsAppMessage $broadcast): array
    {
        $status = [];
        foreach ($users as $user) {
            $digits = $broadcast->normalizedPhoneDigitsForWapi($user);
            if ($digits === null) {
                $status[(int) $user->id] = 'no_phone';
            } elseif ($this->isInWhatsAppGroup($digits, $phoneIndex)) {
                $status[(int) $user->id] = 'in_group';
            } else {
                $status[(int) $user->id] = 'not_in_group';
            }
        }

        return $status;
    }

    /**
     * @param  array<string, true>  $phoneIndex
     */
    public function isInWhatsAppGroup(string $studentDigits, array $phoneIndex): bool
    {
        foreach ($this->phoneMatchKeys($studentDigits) as $key) {
            if (isset($phoneIndex[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    public function phoneMatchKeys(string $digits): array
    {
        $digits = WapiPhoneNormalizer::normalize($digits);
        if ($digits === '') {
            return [];
        }

        $keys = [$digits];
        foreach ([12, 11, 10, 9] as $len) {
            if (strlen($digits) >= $len) {
                $keys[] = substr($digits, -$len);
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @return array<string, mixed>
     */
    private function studentRow(User $student, ?string $digits): array
    {
        $groupNames = $student->courseGroupMemberships
            ->map(fn ($m) => $m->group?->name)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $courseNames = $student->courseEnrollments
            ->where('enrollment_status', 'active')
            ->map(fn ($e) => $e->course?->title)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'phone' => $digits ?? ($student->full_phone ?? $student->phone ?? '—'),
            'phone_digits' => $digits,
            'phone_display' => $student->full_phone ?? trim(($student->country_code ?? '') . ($student->phone ?? '')) ?: $student->phone,
            'groups' => $groupNames,
            'courses' => $courseNames,
        ];
    }

    public function resolveLabels(?int $courseId, ?int $platformGroupId): array
    {
        return [
            'course' => $courseId ? Course::find($courseId)?->title : null,
            'platform_group' => $platformGroupId ? CourseGroup::find($platformGroupId)?->name : null,
        ];
    }
}
