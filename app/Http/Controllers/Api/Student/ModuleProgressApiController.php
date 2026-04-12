<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Services\Api\StudentModuleProgressApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * إكمال/إلغاء إكمال وحدات الكورس للتطبيق — مسارات /api فقط.
 */
class ModuleProgressApiController extends Controller
{
    public function __construct(
        protected StudentModuleProgressApiService $progressApi
    ) {}

    public function markComplete(Request $request, int $moduleId): JsonResponse
    {
        $user = $request->user();
        $result = $this->progressApi->markModuleComplete($user, $moduleId);

        $httpStatus = (int) ($result['http_status'] ?? 200);

        $body = [
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['success'] ? [
                'module_id' => $result['module_id'],
                'is_completed' => $result['is_completed'],
                'completion_percentage' => $result['completion_percentage'],
            ] : null,
        ];

        return response()->json($body, $httpStatus >= 400 ? $httpStatus : 200);
    }

    public function markIncomplete(Request $request, int $moduleId): JsonResponse
    {
        $user = $request->user();
        $result = $this->progressApi->markModuleIncomplete($user, $moduleId);

        $httpStatus = (int) ($result['http_status'] ?? 200);

        $body = [
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['success'] ? [
                'module_id' => $result['module_id'],
                'is_completed' => $result['is_completed'],
                'completion_percentage' => $result['completion_percentage'],
            ] : null,
        ];

        return response()->json($body, $httpStatus >= 400 ? $httpStatus : 200);
    }
}
