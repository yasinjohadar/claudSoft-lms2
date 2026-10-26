<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Services\Api\StudentModuleProgressApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * تحديد الدرس كمكتمل / إلغاء الإكمال (نفس منطق API الطالب: تحديث القسم والكورس والأحداث).
 */
class ModuleCompletionController extends BaseMobileController
{
    public function __construct(
        protected StudentModuleProgressApiService $progress,
    ) {}

    public function store(Request $request, int $module): JsonResponse
    {
        return $this->respond($this->progress->markModuleComplete($request->user(), $module));
    }

    public function destroy(Request $request, int $module): JsonResponse
    {
        return $this->respond($this->progress->markModuleIncomplete($request->user(), $module));
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function respond(array $result): JsonResponse
    {
        if (! $result['success']) {
            return $this->error($result['message'], (int) ($result['http_status'] ?? 422));
        }

        return $this->success([
            'module_id' => $result['module_id'],
            'status' => $result['is_completed'] ? 'completed' : 'in_progress',
            'course_completion_percentage' => $result['completion_percentage'],
        ], $result['message']);
    }
}
