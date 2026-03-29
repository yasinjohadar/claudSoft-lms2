<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CleansUtf8AiResponse;
use App\Http\Controllers\Controller;
use App\Models\AIModel;
use App\Models\FrontendCourseCategory;
use App\Models\User;
use App\Services\Ai\AIFrontendCourseService;
use App\Services\Ai\AIModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIFrontendCourseController extends Controller
{
    use CleansUtf8AiResponse;

    public function __construct(
        private AIFrontendCourseService $courseAiService,
        private AIModelService $modelService
    ) {}

    public function create(Request $request)
    {
        $categories = FrontendCourseCategory::where('is_active', true)->orderBy('name')->get();
        $instructors = User::role(['instructor', 'admin'])->orderBy('name')->get();
        $models = $this->modelService->getAvailableModels('all');

        return view('admin.frontend-courses.ai-create', compact('categories', 'instructors', 'models'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:'.AIFrontendCourseService::MAX_TOPIC_CHARS,
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'tone' => 'nullable|in:professional,friendly,technical,casual,formal',
            'language' => 'nullable|in:ar,en',
            'category_id' => 'required|exists:frontend_course_categories,id',
            'instructor_id' => 'required|exists:users,id',
            'level' => 'required|in:beginner,intermediate,advanced',
            'target_sections' => 'nullable|integer|min:2|max:12',
            'lessons_per_section_hint' => 'nullable|integer|min:1|max:8',
            'generate_advanced_seo' => 'boolean',
        ]);

        try {
            $model = $validated['ai_model_id']
                ? AIModel::find($validated['ai_model_id'])
                : $this->modelService->getDefaultModel();

            if (! $model) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد موديل AI متاح',
                ], 400);
            }

            $category = FrontendCourseCategory::find($validated['category_id']);

            $data = $this->courseAiService->generateCourseOutline(
                $validated['topic'],
                $model,
                [
                    'tone' => $validated['tone'] ?? 'professional',
                    'language' => $validated['language'] ?? 'ar',
                    'level' => $validated['level'],
                    'category_name' => $category?->name,
                    'target_sections' => $validated['target_sections'] ?? null,
                    'lessons_per_section_hint' => $validated['lessons_per_section_hint'] ?? null,
                    'generate_advanced_seo' => $validated['generate_advanced_seo'] ?? true,
                ]
            );

            $data = $this->cleanUtf8Data($data);

            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('AI frontend course generate: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'timeout') || str_contains($errorMessage, 'Timeout')) {
                $userMessage = 'انتهت مهلة الاتصال. جرّب مرة أخرى أو بسّط الموضوع.';
            } elseif (str_contains($errorMessage, 'API Key') || str_contains(strtolower($errorMessage), 'api key')) {
                $userMessage = 'مشكلة في API Key. تحقق من إعدادات الموديل.';
            } elseif (str_contains($errorMessage, 'quota') || str_contains($errorMessage, 'رصيد')) {
                $userMessage = 'رصيد الموديل غير كافٍ.';
            } else {
                $userMessage = 'حدث خطأ أثناء التوليد: '.$errorMessage;
            }

            return response()->json([
                'success' => false,
                'message' => $userMessage,
                'error_details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
