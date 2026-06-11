<?php

namespace App\Services\StudentGifts;

use App\Models\GamificationNotification;
use App\Models\StudentGift;
use App\Models\StudentGiftRecipient;
use App\Models\User;
use App\Services\Gamification\BadgeManualAwardService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentGiftGrantService
{
    public function __construct(
        protected BadgeManualAwardService $targetingService
    ) {}

    public function resolveTargetStudents(string $targetType, array $payload): Collection
    {
        return $this->targetingService->resolveTargetStudents($targetType, $payload);
    }

    public function previewRecipients(StudentGift $gift): array
    {
        if (! $gift->target_type || ! is_array($gift->target_payload)) {
            return [
                'total' => 0,
                'already_have' => 0,
                'will_grant' => 0,
            ];
        }

        $students = $this->resolveTargetStudents($gift->target_type, $gift->target_payload);
        $total = $students->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'already_have' => 0,
                'will_grant' => 0,
            ];
        }

        $alreadyHave = StudentGiftRecipient::query()
            ->where('student_gift_id', $gift->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->count();

        return [
            'total' => $total,
            'already_have' => $alreadyHave,
            'will_grant' => max(0, $total - $alreadyHave),
        ];
    }

    public function previewFromRequest(string $targetType, array $payload, ?StudentGift $gift = null): array
    {
        $students = $this->resolveTargetStudents($targetType, $payload);
        $total = $students->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'already_have' => 0,
                'will_grant' => 0,
            ];
        }

        $alreadyHave = 0;
        if ($gift) {
            $alreadyHave = StudentGiftRecipient::query()
                ->where('student_gift_id', $gift->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->count();
        }

        return [
            'total' => $total,
            'already_have' => $alreadyHave,
            'will_grant' => max(0, $total - $alreadyHave),
        ];
    }

    public function grant(StudentGift $gift, ?int $grantedBy = null): array
    {
        $students = $this->resolveTargetStudents(
            (string) $gift->target_type,
            $gift->target_payload ?? []
        );

        return DB::transaction(function () use ($gift, $students, $grantedBy) {
            $now = now();
            $granted = 0;
            $skipped = 0;
            $newStudentIds = [];

            foreach ($students as $student) {
                $recipient = StudentGiftRecipient::query()->firstOrNew([
                    'student_gift_id' => $gift->id,
                    'student_id' => $student->id,
                ]);

                if ($recipient->exists) {
                    $skipped++;

                    continue;
                }

                $recipient->granted_at = $now;
                $recipient->save();
                $newStudentIds[] = $student->id;
                $granted++;
            }

            $gift->update([
                'status' => StudentGift::STATUS_GRANTED,
                'granted_at' => $gift->granted_at ?? $now,
                'granted_by' => $grantedBy ?? $gift->granted_by,
                'last_regranted_at' => $gift->isGranted() ? $now : $gift->last_regranted_at,
            ]);

            $this->notifyRecipients($gift, $newStudentIds);

            return [
                'granted' => $granted,
                'skipped' => $skipped,
                'total' => $students->count(),
            ];
        });
    }

    public function regrant(StudentGift $gift, ?int $grantedBy = null): array
    {
        if ($gift->isRevoked()) {
            return $this->restoreAfterRevoke($gift, $grantedBy);
        }

        if (! $gift->isGranted()) {
            return $this->grant($gift, $grantedBy);
        }

        $students = $this->resolveTargetStudents(
            (string) $gift->target_type,
            $gift->target_payload ?? []
        );

        return DB::transaction(function () use ($gift, $students, $grantedBy) {
            $now = now();
            $granted = 0;
            $skipped = 0;
            $newStudentIds = [];

            foreach ($students as $student) {
                $recipient = StudentGiftRecipient::query()->firstOrNew([
                    'student_gift_id' => $gift->id,
                    'student_id' => $student->id,
                ]);

                if ($recipient->exists) {
                    $skipped++;

                    continue;
                }

                $recipient->granted_at = $now;
                $recipient->save();
                $newStudentIds[] = $student->id;
                $granted++;
            }

            $gift->update([
                'last_regranted_at' => $now,
                'granted_by' => $grantedBy ?? $gift->granted_by,
            ]);

            $this->notifyRecipients($gift, $newStudentIds);

            return [
                'granted' => $granted,
                'skipped' => $skipped,
                'total' => $students->count(),
            ];
        });
    }

    public function revoke(StudentGift $gift): void
    {
        $gift->update([
            'status' => StudentGift::STATUS_REVOKED,
        ]);
    }

    protected function restoreAfterRevoke(StudentGift $gift, ?int $grantedBy = null): array
    {
        $students = $this->resolveTargetStudents(
            (string) $gift->target_type,
            $gift->target_payload ?? []
        );

        return DB::transaction(function () use ($gift, $students, $grantedBy) {
            $now = now();
            $granted = 0;
            $skipped = 0;
            $newStudentIds = [];

            foreach ($students as $student) {
                $recipient = StudentGiftRecipient::query()->firstOrNew([
                    'student_gift_id' => $gift->id,
                    'student_id' => $student->id,
                ]);

                if ($recipient->exists) {
                    $skipped++;

                    continue;
                }

                $recipient->granted_at = $now;
                $recipient->save();
                $newStudentIds[] = $student->id;
                $granted++;
            }

            $gift->update([
                'status' => StudentGift::STATUS_GRANTED,
                'granted_at' => $gift->granted_at ?? $now,
                'last_regranted_at' => $now,
                'granted_by' => $grantedBy ?? $gift->granted_by,
            ]);

            $this->notifyRecipients($gift, $newStudentIds);

            return [
                'granted' => $granted,
                'skipped' => $skipped,
                'restored' => $skipped,
                'total' => $students->count(),
                'was_revoked' => true,
            ];
        });
    }

    protected function notifyRecipients(StudentGift $gift, array $studentIds): void
    {
        if ($studentIds === []) {
            return;
        }

        $students = User::query()->whereIn('id', $studentIds)->get();

        foreach ($students as $student) {
            GamificationNotification::create([
                'user_id' => $student->id,
                'type' => 'gift_received',
                'title' => 'هدية جديدة!',
                'message' => "تم منحك هدية: {$gift->name}",
                'icon' => 'ri ri-gift-line',
                'action_url' => route('student.gifts.index'),
                'related_type' => StudentGift::class,
                'related_id' => $gift->id,
            ]);
        }
    }
}
