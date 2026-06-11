<?php

namespace App\Services\Gamification;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PointsBulkGrantService
{
    public const OPERATION_BONUS = 'bonus';

    public const OPERATION_DEDUCT = 'deduct';

    public const OPERATION_BACKFILL = 'backfill';

    public function __construct(
        protected BadgeManualAwardService $targetService,
        protected PointsService $pointsService,
        protected PointsBackfillService $backfillService
    ) {}

    public function resolveStudents(string $targetType, array $params): Collection
    {
        return $this->targetService->resolveTargetStudents($targetType, $params);
    }

    public function preview(string $targetType, array $params, string $operation, int $points = 0): array
    {
        $students = $this->resolveStudents($targetType, $params);
        $total = $students->count();

        if ($operation === self::OPERATION_BACKFILL) {
            return [
                'total_students' => $total,
                'operation' => $operation,
                'message' => 'سيتم فحص نشاط كل طالب ومنح النقاط الناقصة فقط (بدون تكرار).',
            ];
        }

        if ($points === 0) {
            throw ValidationException::withMessages([
                'points' => 'يرجى إدخال عدد نقاط غير صفري.',
            ]);
        }

        $absPoints = abs($points);

        return [
            'total_students' => $total,
            'points_per_student' => $absPoints,
            'total_points' => $total * $absPoints,
            'operation' => $operation,
        ];
    }

    public function execute(
        string $targetType,
        array $params,
        string $operation,
        int $points,
        string $reason,
        User $admin
    ): array {
        $students = $this->resolveStudents($targetType, $params);

        if ($students->isEmpty()) {
            throw ValidationException::withMessages([
                'target_type' => 'لا يوجد طلاب مطابقون للاستهداف المحدد.',
            ]);
        }

        if ($operation === self::OPERATION_BACKFILL) {
            return $this->backfillService->backfillStudents($students, $reason, $admin);
        }

        if ($points === 0) {
            throw ValidationException::withMessages([
                'points' => 'يرجى إدخال عدد نقاط غير صفري.',
            ]);
        }

        $awarded = 0;
        $failed = 0;
        $totalPoints = 0;

        foreach ($students as $student) {
            try {
                if ($operation === self::OPERATION_BONUS) {
                    $transaction = $this->pointsService->awardBonus(
                        $student,
                        abs($points),
                        $reason,
                        $admin
                    );
                } else {
                    $transaction = $this->pointsService->deductPoints(
                        $student,
                        abs($points),
                        'admin_adjustment',
                        $reason,
                        User::class,
                        $student->id
                    );
                }

                if ($transaction) {
                    $awarded++;
                    $totalPoints += abs($points);
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                report($e);
            }
        }

        return [
            'operation' => $operation,
            'total_students' => $students->count(),
            'awarded' => $awarded,
            'failed' => $failed,
            'total_points' => $totalPoints,
        ];
    }
}
