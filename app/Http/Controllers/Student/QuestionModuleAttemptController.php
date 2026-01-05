<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QuestionModule;
use App\Models\QuestionModuleAttempt;
use App\Models\QuestionModuleResponse;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionModuleAttemptController extends Controller
{
    /**
     * Start a new attempt for a question module.
     */
    public function start($questionModuleId)
    {
        // Get module_id from request (passed as query parameter)
        $moduleIdFromRequest = request()->get('module_id');
        
        // Store the referer URL for error redirects
        $referer = request()->headers->get('referer');
        $moduleIdFromReferer = null;
        
        // Try to extract module ID from referer URL
        if ($referer && preg_match('/\/student\/learn\/modules\/(\d+)/', $referer, $matches)) {
            $moduleIdFromReferer = $matches[1];
        }
        
        // Use module_id from request, referer, or null
        $fallbackModuleId = $moduleIdFromRequest ?? $moduleIdFromReferer;
        
        try {
            $student = auth()->user();
            $questionModule = QuestionModule::with(['questions.questionType', 'questions.options'])
                ->findOrFail($questionModuleId);

            // Get course module for redirect on error
            $courseModule = $questionModule->courseModules()->first();
            $redirectToModule = function($message) use ($courseModule, $fallbackModuleId) {
                if ($courseModule) {
                    return redirect()->route('student.learn.module', $courseModule->id)
                        ->with('error', $message);
                }
                // If no course module, try to redirect to the module from request/referer
                if ($fallbackModuleId) {
                    return redirect()->route('student.learn.module', $fallbackModuleId)
                        ->with('error', $message);
                }
                return redirect()->route('student.dashboard')
                    ->with('error', $message);
            };

            // Check if module is available
            if (!$questionModule->isAvailable()) {
                return $redirectToModule('هذا الاختبار غير متاح حالياً');
            }

            // Check enrollment
            if ($courseModule) {
                $enrollment = CourseEnrollment::where('course_id', $courseModule->course_id)
                    ->where('student_id', $student->id)
                    ->first();

                if (!$enrollment || !$enrollment->isActive()) {
                    return $redirectToModule('أنت غير مسجل في هذا الكورس');
                }
            }

            // Check if student can attempt
            if (!$questionModule->canStudentAttempt($student->id)) {
                return $redirectToModule('لقد استنفدت جميع المحاولات المسموحة');
            }

            // Check if there's an in-progress attempt
            $inProgressAttempt = $questionModule->studentAttempts($student->id)
                ->where('status', 'in_progress')
                ->first();

            if ($inProgressAttempt) {
                return redirect()->route('student.question-module.take', $inProgressAttempt->id);
            }

            // Create new attempt
            DB::beginTransaction();
            try {
                $attemptNumber = $questionModule->studentAttempts($student->id)->count() + 1;

                // Prepare questions order
                $questions = $questionModule->questions;
                $questionIds = $questions->pluck('id')->toArray();

                // Shuffle if required
                if ($questionModule->shuffle_questions) {
                    shuffle($questionIds);
                }

                $attempt = QuestionModuleAttempt::create([
                    'question_module_id' => $questionModule->id,
                    'student_id' => $student->id,
                    'attempt_number' => $attemptNumber,
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'question_order' => $questionIds,
                ]);

                // Create response records for all questions
                foreach ($questionIds as $questionId) {
                    $question = $questions->find($questionId);
                    QuestionModuleResponse::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                        'max_score' => $question->pivot->question_grade,
                    ]);
                }

                DB::commit();

                return redirect()->route('student.question-module.take', $attempt->id);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            // Get course module for redirect on error
            $questionModule = QuestionModule::find($questionModuleId);
            $courseModule = $questionModule ? $questionModule->courseModules()->first() : null;
            
            if ($courseModule) {
                return redirect()->route('student.learn.module', $courseModule->id)
                    ->with('error', 'حدث خطأ أثناء بدء الاختبار: ' . $e->getMessage());
            }
            
            // If no course module, try to redirect to the module from request/referer
            if ($fallbackModuleId) {
                return redirect()->route('student.learn.module', $fallbackModuleId)
                    ->with('error', 'حدث خطأ أثناء بدء الاختبار: ' . $e->getMessage());
            }
            
            return redirect()->route('student.dashboard')
                ->with('error', 'حدث خطأ أثناء بدء الاختبار: ' . $e->getMessage());
        }
    }

    /**
     * Take the attempt (show questions page).
     */
    public function take($attemptId)
    {
        try {
            $student = auth()->user();
            $attempt = QuestionModuleAttempt::with([
                'questionModule.questions.questionType',
                'questionModule.questions.options',
                'responses.question.questionType',
                'responses.question.options'
            ])->findOrFail($attemptId);

            // Check ownership
            if ($attempt->student_id !== $student->id) {
                return redirect()->route('student.dashboard')
                    ->with('error', 'غير مصرح لك بالوصول لهذا الاختبار');
            }

            // Check if already completed
            if ($attempt->isCompleted()) {
                return redirect()->route('student.question-module.result', $attempt->id);
            }

            // Check if time is up
            if ($attempt->isTimeUp()) {
                $this->submitAttempt($attempt, true);
                return redirect()->route('student.question-module.result', $attempt->id)
                    ->with('warning', 'انتهى الوقت المحدد للاختبار وتم إرسال إجاباتك تلقائياً');
            }

            // Get questions in order
            $questionOrder = $attempt->question_order;
            $questions = collect();
            foreach ($questionOrder as $questionId) {
                $question = $attempt->questionModule->questions->find($questionId);
                if ($question) {
                    $questions->push($question);
                }
            }

            $remainingTime = $attempt->getRemainingTime();

            return view('student.question-modules.take', compact('attempt', 'questions', 'remainingTime'));
        } catch (\Exception $e) {
            // Get course module for redirect on error
            try {
                $attempt = QuestionModuleAttempt::with('questionModule')->find($attemptId);
                $courseModule = null;
                
                if ($attempt && $attempt->questionModule) {
                    $courseModule = $attempt->questionModule->courseModules()->first();
                }
                
                if ($courseModule) {
                    return redirect()->route('student.learn.module', $courseModule->id)
                        ->with('error', 'حدث خطأ أثناء تحميل الاختبار: ' . $e->getMessage());
                }
            } catch (\Exception $ex) {
                // If we can't get the module, just redirect to dashboard
            }
            
            return redirect()->route('student.dashboard')
                ->with('error', 'حدث خطأ أثناء تحميل الاختبار: ' . $e->getMessage());
        }
    }

    /**
     * Save answer for a question (AJAX).
     */
    public function saveAnswer(Request $request, $attemptId)
    {
        try {
            $validated = $request->validate([
                'question_id' => 'required|exists:question_bank,id',
                'answer' => 'required',
            ]);

            $student = auth()->user();
            $attempt = QuestionModuleAttempt::findOrFail($attemptId);

            // Check ownership
            if ($attempt->student_id !== $student->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح',
                ], 403);
            }

            // Check if in progress
            if (!$attempt->isInProgress()) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الاختبار منتهي',
                ], 400);
            }

            // Check if time is up
            if ($attempt->isTimeUp()) {
                $this->submitAttempt($attempt, true);
                return response()->json([
                    'success' => false,
                    'message' => 'انتهى الوقت المحدد',
                    'time_up' => true,
                ], 400);
            }

            // Find response
            $response = $attempt->responses()->where('question_id', $validated['question_id'])->first();

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'السؤال غير موجود',
                ], 404);
            }

            // Save answer
            $response->update([
                'student_answer' => $validated['answer'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الإجابة',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit the attempt.
     */
    public function submit(Request $request, $attemptId)
    {
        try {
            $student = auth()->user();
            $attempt = QuestionModuleAttempt::with(['responses', 'questionModule'])
                ->findOrFail($attemptId);

            // Check ownership
            if ($attempt->student_id !== $student->id) {
                return redirect()->route('student.dashboard')
                    ->with('error', 'غير مصرح لك بإرسال هذا الاختبار');
            }

            // Check if already completed
            if ($attempt->isCompleted()) {
                return redirect()->route('student.question-module.result', $attempt->id);
            }

            $this->submitAttempt($attempt, false);

            return redirect()->route('student.question-module.result', $attempt->id)
                ->with('success', 'تم إرسال الاختبار بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الاختبار: ' . $e->getMessage());
        }
    }

    /**
     * Show attempt result.
     */
    public function result($attemptId)
    {
        try {
            $student = auth()->user();
            $attempt = QuestionModuleAttempt::with([
                'questionModule.questions.questionType',
                'questionModule.questions.options',
                'responses.question.questionType',
                'responses.question.options'
            ])->findOrFail($attemptId);

            // Check ownership
            if ($attempt->student_id !== $student->id) {
                return redirect()->route('student.dashboard')
                    ->with('error', 'غير مصرح لك بالوصول لهذه النتيجة');
            }

            // Check if completed
            if (!$attempt->isCompleted()) {
                return redirect()->route('student.question-module.take', $attempt->id)
                    ->with('error', 'يجب إنهاء الاختبار أولاً');
            }

            // Get questions with responses in order
            $questionOrder = $attempt->question_order;
            $questionsWithResponses = collect();
            foreach ($questionOrder as $questionId) {
                $question = $attempt->questionModule->questions->find($questionId);
                if ($question) {
                    $response = $attempt->responses->where('question_id', $questionId)->first();
                    $questionsWithResponses->push([
                        'question' => $question,
                        'response' => $response,
                    ]);
                }
            }

            $showResults = $attempt->questionModule->show_results;

            return view('student.question-modules.result', compact('attempt', 'questionsWithResponses', 'showResults'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحميل النتيجة: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Submit attempt and grade responses.
     */
    private function submitAttempt(QuestionModuleAttempt $attempt, bool $isTimeUp)
    {
        DB::beginTransaction();
        try {
            // Calculate time spent
            $timeSpent = $attempt->started_at ? now()->diffInSeconds($attempt->started_at) : 0;

            // Grade all responses
            foreach ($attempt->responses as $response) {
                if ($response->student_answer) {
                    $response->gradeResponse();
                }
            }

            // Mark as completed
            $attempt->markAsCompleted();
            $attempt->update(['time_spent' => $timeSpent]);

            // Calculate scores
            $attempt->calculateScores();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
