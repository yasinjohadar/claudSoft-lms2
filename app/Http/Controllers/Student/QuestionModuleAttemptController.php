<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QuestionModule;
use App\Models\QuestionModuleAttempt;
use App\Models\QuestionModuleResponse;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionModuleAttemptController extends Controller
{
    /**
     * Start a new attempt for a question module.
     */
    public function start($questionModule)
    {
        // CRITICAL: Log immediately at the start of the method
        try {
            Log::info('=== QuestionModuleAttemptController::start METHOD CALLED ===', [
                'question_module_param' => $questionModule,
                'question_module_type' => gettype($questionModule),
                'question_module_class' => is_object($questionModule) ? get_class($questionModule) : 'not_object',
                'user_id' => auth()->id(),
                'user_authenticated' => auth()->check(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'referer' => request()->headers->get('referer'),
                'ip' => request()->ip(),
            ]);
        } catch (\Exception $logError) {
            // If logging fails, at least try to write to a file
            @file_put_contents(storage_path('logs/start_method_debug.log'), 
                date('Y-m-d H:i:s') . " - Method called with param: " . var_export($questionModule, true) . "\n", 
                FILE_APPEND
            );
        }
        
        // Initialize variables for error handling
        $questionModuleId = null;
        $fallbackModuleId = null;
        $courseModule = null;
        
        try {
            // Handle both ID and model binding - more robust handling
            if (is_numeric($questionModule)) {
                $questionModuleId = (int)$questionModule;
            } elseif (is_object($questionModule) && method_exists($questionModule, 'getKey')) {
                $questionModuleId = $questionModule->getKey();
            } elseif (is_object($questionModule) && isset($questionModule->id)) {
                $questionModuleId = (int)$questionModule->id;
            } else {
                // Try to convert to int
                $questionModuleId = (int)$questionModule;
            }
            
            Log::info('Question module ID extracted', [
                'question_module_id' => $questionModuleId,
                'original_param' => $questionModule,
            ]);
            
            // Validate questionModuleId
            if (!$questionModuleId || $questionModuleId <= 0) {
                Log::error('Invalid question module ID after extraction', [
                    'question_module_param' => $questionModule,
                    'question_module_type' => gettype($questionModule),
                    'extracted_id' => $questionModuleId,
                    'user_id' => auth()->id(),
                ]);
                
                // Try to get from route parameter directly
                $routeParam = request()->route('questionModule');
                if ($routeParam && is_numeric($routeParam)) {
                    $questionModuleId = (int)$routeParam;
                    Log::info('Got question module ID from route parameter', ['id' => $questionModuleId]);
                } else {
                    // Try to get from URL segment
                    $urlSegments = request()->segments();
                    foreach ($urlSegments as $segment) {
                        if (is_numeric($segment) && $segment > 0) {
                            $questionModuleId = (int)$segment;
                            Log::info('Got question module ID from URL segment', ['id' => $questionModuleId, 'segment' => $segment]);
                            break;
                        }
                    }
                }
                
                if (!$questionModuleId || $questionModuleId <= 0) {
                    Log::error('Could not extract question module ID from any source', [
                        'route_param' => $routeParam,
                        'url_segments' => $urlSegments,
                        'full_url' => request()->fullUrl(),
                    ]);
                    return $this->handleError('معرف الاختبار غير صحيح', null);
                }
            }
            
            // Validate student authentication early
            $student = auth()->user();
            if (!$student) {
                Log::error('No authenticated user in start method - early validation');
                return redirect()->route('login')
                    ->with('error', 'يجب تسجيل الدخول أولاً');
            }
            
            // Validate student role
            if (!$student->hasRole('student')) {
                Log::error('User does not have student role', [
                    'user_id' => $student->id,
                    'roles' => $student->getRoleNames(),
                ]);
                return redirect()->route('student.dashboard')
                    ->with('error', 'ليس لديك صلاحية للوصول إلى هذه الصفحة');
            }
            
            Log::info('QuestionModuleAttemptController::start called', [
                'question_module_id' => $questionModuleId,
                'question_module_param' => $questionModule,
                'question_module_type' => gettype($questionModule),
                'user_id' => auth()->id(),
                'url' => request()->fullUrl(),
                'referer' => request()->headers->get('referer'),
            ]);

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
            
            Log::info('Fallback module ID', [
                'module_id_from_request' => $moduleIdFromRequest,
                'module_id_from_referer' => $moduleIdFromReferer,
                'fallback_module_id' => $fallbackModuleId,
            ]);
            
            Log::info('Loading question module', ['question_module_id' => $questionModuleId]);
            
            // If $questionModule is already a model instance, use it; otherwise load it
            if (!($questionModule instanceof QuestionModule)) {
                $questionModule = QuestionModule::with(['questions.questionType', 'questions.options'])
                    ->findOrFail($questionModuleId);
            } else {
                $questionModule->load(['questions.questionType', 'questions.options']);
            }
            
            Log::info('Question module loaded', [
                'question_module_id' => $questionModule->id,
                'title' => $questionModule->title,
                'questions_count' => $questionModule->questions->count(),
            ]);

            // Get course module for redirect on error
            $courseModule = $questionModule->courseModules()->first();
            
            // Create redirect helper function
            $redirectToModule = function($message) use ($courseModule, $fallbackModuleId, $questionModuleId) {
                return $this->handleError($message, $courseModule, $fallbackModuleId, $questionModuleId);
            };

            // Check if module is available
            if (!$questionModule->isAvailable()) {
                Log::warning('Question module not available', [
                    'question_module_id' => $questionModule->id,
                    'student_id' => $student->id,
                ]);
                return $redirectToModule('هذا الاختبار غير متاح حالياً');
            }

            // Check enrollment
            if ($courseModule) {
                $enrollment = CourseEnrollment::where('course_id', $courseModule->course_id)
                    ->where('student_id', $student->id)
                    ->first();

                if (!$enrollment || !$enrollment->isActive()) {
                    Log::warning('Student not enrolled or enrollment inactive', [
                        'question_module_id' => $questionModule->id,
                        'course_module_id' => $courseModule->id,
                        'student_id' => $student->id,
                        'enrollment_exists' => $enrollment ? true : false,
                        'enrollment_active' => $enrollment ? $enrollment->isActive() : false,
                    ]);
                    return $redirectToModule('أنت غير مسجل في هذا الكورس');
                }
                
                Log::info('Enrollment verified', [
                    'enrollment_id' => $enrollment->id,
                    'course_id' => $courseModule->course_id,
                    'student_id' => $student->id,
                ]);
            } else {
                Log::warning('No course module found for question module', [
                    'question_module_id' => $questionModule->id,
                ]);
            }

            // Check if student can attempt
            if (!$questionModule->canStudentAttempt($student->id)) {
                $attemptsCount = $questionModule->studentAttempts($student->id)->where('status', 'completed')->count();
                Log::warning('Student cannot attempt - max attempts reached', [
                    'question_module_id' => $questionModule->id,
                    'student_id' => $student->id,
                    'attempts_count' => $attemptsCount,
                    'attempts_allowed' => $questionModule->attempts_allowed,
                ]);
                return $redirectToModule('لقد استنفدت جميع المحاولات المسموحة');
            }

            // Check if there's an in-progress attempt
            $inProgressAttempt = $questionModule->studentAttempts($student->id)
                ->where('status', 'in_progress')
                ->first();

            if ($inProgressAttempt) {
                $redirectUrl = route('student.question-module.take', $inProgressAttempt->id);
                Log::info('Redirecting to in-progress attempt', [
                    'attempt_id' => $inProgressAttempt->id,
                    'question_module_id' => $questionModule->id,
                    'student_id' => $student->id,
                    'redirect_url' => $redirectUrl,
                ]);
                
                // Store fallback module ID in session for error handling
                if ($fallbackModuleId) {
                    session()->put('fallback_module_id', $fallbackModuleId);
                }
                
                return redirect($redirectUrl);
            }

            // Check if module has questions
            $questions = $questionModule->questions;
            if ($questions->isEmpty()) {
                Log::error('Question module has no questions', [
                    'question_module_id' => $questionModule->id,
                    'student_id' => $student->id,
                ]);
                return $redirectToModule('هذا الاختبار لا يحتوي على أسئلة');
            }
            
            Log::info('Starting attempt creation', [
                'question_module_id' => $questionModule->id,
                'questions_count' => $questions->count(),
                'student_id' => $student->id,
            ]);

            // Create new attempt
            DB::beginTransaction();
            try {
                $attemptNumber = $questionModule->studentAttempts($student->id)->count() + 1;

                // Prepare questions order
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
                    if (!$question) {
                        throw new \Exception("السؤال رقم {$questionId} غير موجود");
                    }
                    
                    $grade = $question->pivot->question_grade ?? 1.0;
                    
                    QuestionModuleResponse::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                        'max_score' => $grade,
                    ]);
                }

                DB::commit();

                Log::info('Attempt created successfully', [
                    'attempt_id' => $attempt->id,
                    'question_module_id' => $questionModuleId,
                    'student_id' => $student->id,
                    'attempt_number' => $attemptNumber,
                    'questions_count' => count($questionIds),
                ]);

                return redirect()->route('student.question-module.take', $attempt->id);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error creating question module attempt (inner catch)', [
                    'question_module_id' => $questionModuleId,
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Question module not found', [
                'question_module_id' => $questionModuleId ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            // Try to get course module for redirect
            if ($questionModuleId) {
                try {
                    $questionModule = QuestionModule::find($questionModuleId);
                    if ($questionModule) {
                        $courseModule = $questionModule->courseModules()->first();
                    }
                } catch (\Exception $ex) {
                    Log::error('Failed to get course module for redirect', [
                        'error' => $ex->getMessage(),
                    ]);
                }
            }
            
            return $this->handleError('الاختبار غير موجود', $courseModule, $fallbackModuleId, $questionModuleId);
            
        } catch (\Exception $e) {
            Log::error('Error in QuestionModuleAttemptController::start (outer catch)', [
                'question_module_id' => $questionModuleId ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Try to get course module for redirect
            if ($questionModuleId) {
                try {
                    $questionModule = QuestionModule::find($questionModuleId);
                    if ($questionModule) {
                        $courseModule = $questionModule->courseModules()->first();
                    }
                } catch (\Exception $ex) {
                    Log::error('Failed to get course module for redirect', [
                        'error' => $ex->getMessage(),
                    ]);
                }
            }
            
            $errorMessage = config('app.debug') 
                ? 'حدث خطأ أثناء بدء الاختبار: ' . $e->getMessage()
                : 'حدث خطأ أثناء بدء الاختبار. يرجى المحاولة مرة أخرى أو التواصل مع الدعم الفني.';
            
            return $this->handleError($errorMessage, $courseModule, $fallbackModuleId, $questionModuleId);
        }
    }

    /**
     * Take the attempt (show questions page).
     */
    public function take($attemptId)
    {
        // CRITICAL: Log immediately at the start of the method
        try {
            Log::info('=== QuestionModuleAttemptController::take METHOD CALLED ===', [
                'attempt_id_param' => $attemptId,
                'attempt_id_type' => gettype($attemptId),
                'user_id' => auth()->id(),
                'user_authenticated' => auth()->check(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'referer' => request()->headers->get('referer'),
            ]);
        } catch (\Exception $logError) {
            @file_put_contents(storage_path('logs/take_method_debug.log'), 
                date('Y-m-d H:i:s') . " - Method called with attempt_id: " . var_export($attemptId, true) . "\n", 
                FILE_APPEND
            );
        }
        
        try {
            $student = auth()->user();
            
            if (!$student) {
                Log::error('No authenticated user in take method');
                return redirect()->route('login')
                    ->with('error', 'يجب تسجيل الدخول أولاً');
            }
            
            // Handle attemptId - convert to int if needed
            $attemptIdInt = is_numeric($attemptId) ? (int)$attemptId : (is_object($attemptId) && isset($attemptId->id) ? (int)$attemptId->id : (int)$attemptId);
            
            Log::info('Loading attempt', [
                'attempt_id_param' => $attemptId,
                'attempt_id_type' => gettype($attemptId),
                'attempt_id_int' => $attemptIdInt,
                'student_id' => $student->id,
            ]);
            
            $attempt = QuestionModuleAttempt::with([
                'questionModule.questions.questionType',
                'questionModule.questions.options',
                'responses.question.questionType',
                'responses.question.options'
            ])->find($attemptIdInt);
            
            if (!$attempt) {
                Log::error('Attempt not found', [
                    'attempt_id' => $attemptIdInt,
                    'student_id' => $student->id,
                ]);
                
                // Try to get fallback module ID from session
                $fallbackModuleId = session()->get('fallback_module_id');
                if ($fallbackModuleId) {
                    return redirect()->route('student.learn.module', $fallbackModuleId)
                        ->with('error', 'المحاولة غير موجودة');
                }
                
                return redirect()->route('student.dashboard')
                    ->with('error', 'المحاولة غير موجودة');
            }
            
            Log::info('Attempt loaded', [
                'attempt_id' => $attempt->id,
                'attempt_student_id' => $attempt->student_id,
                'current_student_id' => $student->id,
                'student_id_match' => $attempt->student_id == $student->id,
                'status' => $attempt->status,
                'question_module_id' => $attempt->question_module_id,
            ]);
            
            // Check ownership - use == instead of !== for type flexibility
            if ((int)$attempt->student_id !== (int)$student->id) {
                Log::error('Attempt ownership mismatch', [
                    'attempt_student_id' => $attempt->student_id,
                    'attempt_student_id_type' => gettype($attempt->student_id),
                    'current_student_id' => $student->id,
                    'current_student_id_type' => gettype($student->id),
                    'attempt_id' => $attempt->id,
                ]);
                return redirect()->route('student.dashboard')
                    ->with('error', 'غير مصرح لك بالوصول لهذا الاختبار');
            }

            // Check if already completed
            if ($attempt->isCompleted()) {
                Log::info('Attempt already completed, redirecting to result', [
                    'attempt_id' => $attempt->id,
                ]);
                return redirect()->route('student.question-module.result', $attempt->id);
            }

            // Check if time is up
            if ($attempt->isTimeUp()) {
                Log::info('Time is up, auto-submitting attempt', [
                    'attempt_id' => $attempt->id,
                ]);
                $this->submitAttempt($attempt, true);
                return redirect()->route('student.question-module.result', $attempt->id)
                    ->with('warning', 'انتهى الوقت المحدد للاختبار وتم إرسال إجاباتك تلقائياً');
            }

            // Get questions in order
            $questionOrder = $attempt->question_order ?? [];
            
            if (empty($questionOrder)) {
                // If no question order, get all questions from module
                $questionOrder = $attempt->questionModule->questions->pluck('id')->toArray();
            }
            
            $questions = collect();
            foreach ($questionOrder as $questionId) {
                $question = $attempt->questionModule->questions->find($questionId);
                if ($question) {
                    $questions->push($question);
                }
            }

            // Check if we have questions
            if ($questions->isEmpty()) {
                return redirect()->route('student.dashboard')
                    ->with('error', 'لا توجد أسئلة في هذا الاختبار');
            }

            $remainingTime = $attempt->getRemainingTime();
            
            Log::info('Rendering take view', [
                'attempt_id' => $attempt->id,
                'questions_count' => $questions->count(),
                'remaining_time' => $remainingTime,
            ]);

            return view('student.question-modules.take', compact('attempt', 'questions', 'remainingTime'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Attempt not found in take method', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
            ]);
            
            // Try to get course module for redirect
            try {
                $questionModuleId = request()->get('question_module_id');
                if ($questionModuleId) {
                    $questionModule = QuestionModule::find($questionModuleId);
                    if ($questionModule) {
                        $courseModule = $questionModule->courseModules()->first();
                        if ($courseModule) {
                            return redirect()->route('student.learn.module', $courseModule->id)
                                ->with('error', 'المحاولة غير موجودة');
                        }
                    }
                }
            } catch (\Exception $ex) {
                Log::error('Failed to get course module in take catch', [
                    'error' => $ex->getMessage(),
                ]);
            }
            
            return redirect()->route('student.dashboard')
                ->with('error', 'المحاولة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error in QuestionModuleAttemptController::take', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
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
                Log::error('Failed to get course module in take catch', [
                    'error' => $ex->getMessage(),
                ]);
            }
            
            $errorMessage = config('app.debug') 
                ? 'حدث خطأ أثناء تحميل الاختبار: ' . $e->getMessage()
                : 'حدث خطأ أثناء تحميل الاختبار. يرجى المحاولة مرة أخرى أو التواصل مع الدعم الفني.';
            
            return redirect()->route('student.dashboard')
                ->with('error', $errorMessage);
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

            // Save answer - ensure it's properly formatted
            $answerToSave = $validated['answer'];
            
            // Handle null or empty answers
            if ($answerToSave === null || $answerToSave === '') {
                Log::warning('Attempted to save null or empty answer', [
                    'attempt_id' => $attempt->id,
                    'question_id' => $validated['question_id'],
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'الإجابة فارغة',
                ], 400);
            }
            
            // Log for debugging
            Log::info('=== SAVING ANSWER ===', [
                'attempt_id' => $attempt->id,
                'response_id' => $response->id,
                'question_id' => $validated['question_id'],
                'answer_type' => gettype($answerToSave),
                'answer_value' => is_array($answerToSave) ? json_encode($answerToSave, JSON_UNESCAPED_UNICODE) : $answerToSave,
                'answer_is_array' => is_array($answerToSave),
                'answer_is_empty' => is_array($answerToSave) ? empty($answerToSave) : (trim((string)$answerToSave) === ''),
            ]);
            
            // Update the response
            $updateResult = $response->update([
                'student_answer' => $answerToSave,
            ]);
            
            // Verify it was saved by refreshing from database
            $response->refresh();
            
            // Check if it was actually saved
            $savedAnswer = $response->getOriginal('student_answer'); // Get raw value from DB
            $savedAnswerDecoded = json_decode($savedAnswer, true);
            
            Log::info('=== ANSWER SAVED ===', [
                'response_id' => $response->id,
                'update_result' => $updateResult,
                'saved_raw' => $savedAnswer,
                'saved_decoded' => $savedAnswerDecoded,
                'saved_cast' => $response->student_answer,
                'saved_type' => gettype($response->student_answer),
            ]);
            
            if ($response->student_answer === null || (is_array($response->student_answer) && empty($response->student_answer))) {
                Log::error('Answer was not saved correctly!', [
                    'response_id' => $response->id,
                    'attempt_id' => $attempt->id,
                    'question_id' => $validated['question_id'],
                    'original_answer' => $answerToSave,
                    'saved_answer' => $response->student_answer,
                ]);
            }

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
        // CRITICAL: Log immediately at the start of the method
        try {
            Log::info('=== QuestionModuleAttemptController::submit METHOD CALLED ===', [
                'attempt_id_param' => $attemptId,
                'attempt_id_type' => gettype($attemptId),
                'user_id' => auth()->id(),
                'user_authenticated' => auth()->check(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
            ]);
        } catch (\Exception $logError) {
            @file_put_contents(storage_path('logs/submit_method_debug.log'), 
                date('Y-m-d H:i:s') . " - Method called with attempt_id: " . var_export($attemptId, true) . "\n", 
                FILE_APPEND
            );
        }
        
        try {
            $student = auth()->user();
            
            if (!$student) {
                Log::error('No authenticated user in submit method');
                return redirect()->route('login')
                    ->with('error', 'يجب تسجيل الدخول أولاً');
            }
            
            // Handle attemptId - convert to int if needed
            $attemptIdInt = is_numeric($attemptId) ? (int)$attemptId : (is_object($attemptId) && isset($attemptId->id) ? (int)$attemptId->id : (int)$attemptId);
            
            Log::info('Loading attempt for submit', [
                'attempt_id_param' => $attemptId,
                'attempt_id_int' => $attemptIdInt,
                'student_id' => $student->id,
            ]);
            
            $attempt = QuestionModuleAttempt::with(['responses', 'questionModule'])
                ->find($attemptIdInt);
            
            if (!$attempt) {
                Log::error('Attempt not found in submit method', [
                    'attempt_id' => $attemptIdInt,
                    'student_id' => $student->id,
                ]);
                
                // Try to get fallback module ID from session
                $fallbackModuleId = session()->get('fallback_module_id');
                if ($fallbackModuleId) {
                    return redirect()->route('student.learn.module', $fallbackModuleId)
                        ->with('error', 'المحاولة غير موجودة');
                }
                
                return redirect()->route('student.dashboard')
                    ->with('error', 'المحاولة غير موجودة');
            }
            
            Log::info('Attempt loaded for submit', [
                'attempt_id' => $attempt->id,
                'attempt_student_id' => $attempt->student_id,
                'current_student_id' => $student->id,
                'student_id_match' => (int)$attempt->student_id == (int)$student->id,
            ]);

            // Check ownership - use == instead of !== for type flexibility
            if ((int)$attempt->student_id !== (int)$student->id) {
                Log::error('Attempt ownership mismatch in submit', [
                    'attempt_student_id' => $attempt->student_id,
                    'attempt_student_id_type' => gettype($attempt->student_id),
                    'current_student_id' => $student->id,
                    'current_student_id_type' => gettype($student->id),
                    'attempt_id' => $attempt->id,
                ]);
                return redirect()->route('student.dashboard')
                    ->with('error', 'غير مصرح لك بإرسال هذا الاختبار');
            }

            // Check if already completed
            if ($attempt->isCompleted()) {
                return redirect()->route('student.question-module.result', $attempt->id);
            }

            Log::info('Submitting attempt', [
                'attempt_id' => $attempt->id,
                'student_id' => $student->id,
                'request_data' => $request->all(),
            ]);
            
            // Save answers from request if provided (fallback if AJAX saves failed)
            if ($request->has('answers') && is_array($request->answers)) {
                Log::info('Saving answers from request', [
                    'attempt_id' => $attempt->id,
                    'answers_count' => count($request->answers),
                ]);
                
                foreach ($request->answers as $questionId => $answerJson) {
                    try {
                        $answer = json_decode($answerJson, true);
                        if ($answer === null && json_last_error() !== JSON_ERROR_NONE) {
                            // If JSON decode fails, treat as string
                            $answer = $answerJson;
                        }
                        
                        $response = $attempt->responses()->where('question_id', $questionId)->first();
                        if ($response) {
                            Log::info('Saving answer from request', [
                                'response_id' => $response->id,
                                'question_id' => $questionId,
                                'answer' => is_array($answer) ? json_encode($answer, JSON_UNESCAPED_UNICODE) : $answer,
                            ]);
                            
                            $response->update([
                                'student_answer' => $answer,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error saving answer from request', [
                            'question_id' => $questionId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                // Reload responses after saving
                $attempt->load('responses');
            }
            
            $this->submitAttempt($attempt, false);

            Log::info('Attempt submitted successfully', [
                'attempt_id' => $attempt->id,
                'student_id' => $student->id,
            ]);

            return redirect()->route('student.question-module.result', $attempt->id)
                ->with('success', 'تم إرسال الاختبار بنجاح');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Attempt not found in submit method', [
                'attempt_id' => $attemptId ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            
            // Try to get fallback module ID from session
            $fallbackModuleId = session()->get('fallback_module_id');
            if ($fallbackModuleId) {
                return redirect()->route('student.learn.module', $fallbackModuleId)
                    ->with('error', 'المحاولة غير موجودة');
            }
            
            return redirect()->route('student.dashboard')
                ->with('error', 'المحاولة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error in QuestionModuleAttemptController::submit', [
                'attempt_id' => $attemptId ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = config('app.debug') 
                ? 'حدث خطأ أثناء إرسال الاختبار: ' . $e->getMessage()
                : 'حدث خطأ أثناء إرسال الاختبار. يرجى المحاولة مرة أخرى أو التواصل مع الدعم الفني.';
            
            return redirect()->back()
                ->with('error', $errorMessage);
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

            // Check ownership - use == instead of !== for type flexibility
            if ((int)$attempt->student_id !== (int)$student->id) {
                Log::error('Attempt ownership mismatch in result', [
                    'attempt_student_id' => $attempt->student_id,
                    'current_student_id' => $student->id,
                    'attempt_id' => $attempt->id,
                ]);
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

            // Reload responses with question and questionType to ensure we have the latest data
            $attempt->load(['responses.question.questionType', 'responses.question.options']);
            
            // Log all responses before grading
            Log::info('=== STARTING AUTO-GRADING ===', [
                'attempt_id' => $attempt->id,
                'responses_count' => $attempt->responses->count(),
                'responses_data' => $attempt->responses->map(function($r) {
                    return [
                        'id' => $r->id,
                        'question_id' => $r->question_id,
                        'question_type' => $r->question->questionType->name ?? 'unknown',
                        'has_answer' => !empty($r->student_answer),
                        'answer_type' => gettype($r->student_answer),
                        'answer_value' => is_array($r->student_answer) ? json_encode($r->student_answer, JSON_UNESCAPED_UNICODE) : $r->student_answer,
                        'is_correct_before' => $r->is_correct,
                        'score_obtained_before' => $r->score_obtained,
                    ];
                })->toArray(),
            ]);

            // Grade only auto-gradable responses (skip short_answer and essay)
            foreach ($attempt->responses as $response) {
                // Check if response has an answer
                $hasAnswer = false;
                $studentAnswer = $response->student_answer;
                
                if ($studentAnswer !== null) {
                    if (is_array($studentAnswer)) {
                        // Check if array is not empty
                        $hasAnswer = !empty($studentAnswer);
                    } else {
                        // Check if string is not empty
                        $hasAnswer = trim((string)$studentAnswer) !== '' && $studentAnswer !== 'null' && $studentAnswer !== '[]';
                    }
                }
                
                if ($hasAnswer) {
                    // Load question type if not loaded
                    if (!$response->relationLoaded('question')) {
                        $response->load('question.questionType');
                    }
                    
                    // Check if question type supports auto-grading
                    $questionType = $response->question->questionType->name ?? '';
                    $requiresManualGrading = in_array($questionType, ['short_answer', 'essay']);
                    
                    // Only auto-grade if it doesn't require manual grading
                    if (!$requiresManualGrading) {
                        Log::info('Auto-grading response', [
                            'response_id' => $response->id,
                            'question_id' => $response->question_id,
                            'question_type' => $questionType,
                            'student_answer' => is_array($studentAnswer) ? json_encode($studentAnswer, JSON_UNESCAPED_UNICODE) : $studentAnswer,
                        ]);
                        
                        // Grade the response
                        $result = $response->gradeResponse();
                        
                        // Refresh to get updated values
                        $response->refresh();
                        
                        Log::info('Response graded', [
                            'response_id' => $response->id,
                            'is_correct' => $response->is_correct,
                            'score_obtained' => $response->score_obtained,
                            'grading_result' => $result,
                        ]);
                    } else {
                        Log::info('Skipping manual grading question', [
                            'response_id' => $response->id,
                            'question_id' => $response->question_id,
                            'question_type' => $questionType,
                        ]);
                    }
                    // For short_answer and essay, leave is_correct and score_obtained as null
                } else {
                    Log::warning('Response has no answer - skipping grading', [
                        'response_id' => $response->id,
                        'question_id' => $response->question_id,
                        'student_answer' => $studentAnswer,
                        'student_answer_type' => gettype($studentAnswer),
                    ]);
                }
            }
            
            // Log final state after grading
            Log::info('=== AUTO-GRADING COMPLETED ===', [
                'attempt_id' => $attempt->id,
                'responses_after_grading' => $attempt->responses->map(function($r) {
                    return [
                        'id' => $r->id,
                        'question_id' => $r->question_id,
                        'question_type' => $r->question->questionType->name ?? 'unknown',
                        'is_correct' => $r->is_correct,
                        'score_obtained' => $r->score_obtained,
                        'max_score' => $r->max_score,
                    ];
                })->toArray(),
            ]);

            // Mark as completed
            $attempt->markAsCompleted();
            $attempt->update(['time_spent' => $timeSpent]);

            // Reload responses to ensure we have the latest scores before calculating
            $attempt->load('responses');
            
            Log::info('=== BEFORE CALCULATE SCORES ===', [
                'attempt_id' => $attempt->id,
                'responses_scores' => $attempt->responses->map(function($r) {
                    return [
                        'id' => $r->id,
                        'score_obtained' => $r->score_obtained,
                        'max_score' => $r->max_score,
                    ];
                })->toArray(),
            ]);

            // Calculate scores
            $attempt->calculateScores();
            
            // Reload attempt to get updated scores
            $attempt->refresh();
            
            Log::info('=== AFTER CALCULATE SCORES ===', [
                'attempt_id' => $attempt->id,
                'total_score' => $attempt->total_score,
                'percentage' => $attempt->percentage,
                'is_passed' => $attempt->is_passed,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in submitAttempt', [
                'attempt_id' => $attempt->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Helper: Handle errors and redirect appropriately.
     * 
     * @param string $message Error message to display
     * @param \App\Models\CourseModule|null $courseModule Course module for redirect
     * @param int|null $fallbackModuleId Fallback module ID from request/referer
     * @param int|null $questionModuleId Question module ID for logging
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleError(string $message, $courseModule = null, $fallbackModuleId = null, $questionModuleId = null)
    {
        // Log error with full context
        Log::error('=== HANDLE ERROR CALLED ===', [
            'message' => $message,
            'course_module_id' => $courseModule ? $courseModule->id : null,
            'fallback_module_id' => $fallbackModuleId,
            'question_module_id' => $questionModuleId,
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
            'referer' => request()->headers->get('referer'),
            'session_id' => session()->getId(),
        ]);
        
        // Also write to a debug file as fallback
        try {
            @file_put_contents(storage_path('logs/error_debug.log'), 
                date('Y-m-d H:i:s') . " - Error: " . $message . 
                " | CourseModule: " . ($courseModule ? $courseModule->id : 'null') .
                " | Fallback: " . ($fallbackModuleId ?? 'null') .
                " | QuestionModule: " . ($questionModuleId ?? 'null') . "\n", 
                FILE_APPEND
            );
        } catch (\Exception $e) {
            // Ignore file write errors
        }
        
        // Store error in session with multiple keys to ensure it's displayed
        session()->flash('error', $message);
        session()->flash('error_timestamp', now()->timestamp);
        session()->flash('error_source', 'question_module_start');
        
        // Priority 1: Redirect to course module if available
        if ($courseModule) {
            Log::info('Redirecting to course module', ['course_module_id' => $courseModule->id]);
            $redirect = redirect()->route('student.learn.module', $courseModule->id);
            $redirect->with('error', $message);
            $redirect->with('error_source', 'question_module_start');
            return $redirect;
        }
        
        // Priority 2: Redirect to fallback module from request/referer
        if ($fallbackModuleId) {
            Log::info('Redirecting to fallback module', ['fallback_module_id' => $fallbackModuleId]);
            $redirect = redirect()->route('student.learn.module', $fallbackModuleId);
            $redirect->with('error', $message);
            $redirect->with('error_source', 'question_module_start');
            return $redirect;
        }
        
        // Priority 3: Try to get course module from question module
        if ($questionModuleId) {
            try {
                $questionModule = QuestionModule::find($questionModuleId);
                if ($questionModule) {
                    $courseModule = $questionModule->courseModules()->first();
                    if ($courseModule) {
                        Log::info('Found course module from question module', [
                            'course_module_id' => $courseModule->id,
                            'question_module_id' => $questionModuleId,
                        ]);
                        $redirect = redirect()->route('student.learn.module', $courseModule->id);
                        $redirect->with('error', $message);
                        $redirect->with('error_source', 'question_module_start');
                        return $redirect;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to get course module in handleError', [
                    'question_module_id' => $questionModuleId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        // Last resort: Redirect to dashboard with error message
        Log::error('=== REDIRECTING TO DASHBOARD AS LAST RESORT ===', [
            'message' => $message,
            'question_module_id' => $questionModuleId,
            'fallback_module_id' => $fallbackModuleId,
            'course_module' => $courseModule ? 'exists' : 'null',
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
        ]);
        
        $redirect = redirect()->route('student.dashboard');
        $redirect->with('error', $message);
        $redirect->with('error_source', 'question_module_start');
        $redirect->with('error_details', [
            'question_module_id' => $questionModuleId,
            'fallback_module_id' => $fallbackModuleId,
        ]);
        return $redirect;
    }
}
