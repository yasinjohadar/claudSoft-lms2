<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AIStudentFeedback;
use App\Models\AIModel;
use App\Models\User;
use App\Models\QuizAttempt;
use App\Services\Ai\AIStudentFeedbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIStudentFeedbackController extends Controller
{
    public function __construct(
        private AIStudentFeedbackService $feedbackService
    ) {}

    /**
     * توليد ملاحظات للطالب
     */
    public function generateFeedback(Request $request, User $student)
    {
        $validated = $request->validate([
            'quiz_attempt_id' => 'nullable|exists:quiz_attempts,id',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'feedback_type' => 'nullable|in:performance,general,improvement',
        ]);

        try {
            $attempt = $validated['quiz_attempt_id'] 
                ? QuizAttempt::with('quiz')->find($validated['quiz_attempt_id'])
                : null;

            $model = $validated['ai_model_id']
                ? AIModel::find($validated['ai_model_id'])
                : null;

            $feedback = $this->feedbackService->generateFeedback($student, $attempt, $model);

            return redirect()->route('admin.ai.student-feedback.show', $feedback)
                ->with('success', 'تم توليد الملاحظات بنجاح');

        } catch (\Exception $e) {
            Log::error('Error generating feedback: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء توليد الملاحظات: ' . $e->getMessage());
        }
    }

    /**
     * عرض ملاحظات الطالب
     */
    public function index(Request $request)
    {
        $query = AIStudentFeedback::with(['student', 'quizAttempt.quiz', 'aiModel']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('feedback_type')) {
            $query->where('feedback_type', $request->feedback_type);
        }

        $feedbacks = $query->latest()->paginate(20);

        return view('admin.ai.student-feedback.index', compact('feedbacks'));
    }

    /**
     * صفحة إنشاء ملاحظات جديدة
     */
    public function create()
    {
        $students = User::role('student')
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
        
        $models = AIModel::where('is_active', true)->get();
        
        $feedbackTypes = AIStudentFeedback::FEEDBACK_TYPES;

        return view('admin.ai.student-feedback.create', compact('students', 'models', 'feedbackTypes'));
    }

    /**
     * حفظ وتوليد ملاحظات جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'feedback_type' => 'required|in:performance,general,improvement',
            'custom_prompt' => 'nullable|string|max:1000',
        ]);

        try {
            $student = User::findOrFail($validated['student_id']);
            
            $model = $validated['ai_model_id']
                ? AIModel::find($validated['ai_model_id'])
                : null;

            $feedback = $this->feedbackService->generateFeedback(
                $student, 
                null, 
                $model,
                [
                    'feedback_type' => $validated['feedback_type'],
                    'custom_prompt' => $validated['custom_prompt'] ?? null,
                ]
            );

            return redirect()->route('admin.ai.student-feedback.show', $feedback)
                ->with('success', 'تم توليد الملاحظات بنجاح');

        } catch (\Exception $e) {
            Log::error('Error generating feedback: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء توليد الملاحظات: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض ملاحظة واحدة
     */
    public function show(AIStudentFeedback $studentFeedback)
    {
        $studentFeedback->load(['student', 'quizAttempt.quiz', 'aiModel']);

        return view('admin.ai.student-feedback.show', compact('studentFeedback'));
    }
}

