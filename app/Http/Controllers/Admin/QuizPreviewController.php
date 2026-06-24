<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\Quiz\QuizPreviewFlowService;
use Illuminate\Http\Request;

class QuizPreviewController extends Controller
{
    public function __construct(
        protected QuizPreviewFlowService $previewFlow
    ) {}

    public function start(Request $request, $id)
    {
        $quiz = Quiz::with(['settings', 'quizQuestions.question.questionType'])->findOrFail($id);

        if ($quiz->quizQuestions->isEmpty()) {
            return redirect()
                ->route('quizzes.show', $quiz->id)
                ->withErrors(['error' => 'لا يمكن تجربة اختبار بدون أسئلة. أضف أسئلة أولاً.']);
        }

        try {
            $attempt = $this->previewFlow->startPreviewAttempt($quiz, $request->user(), $request);

            return redirect()
                ->route('quizzes.preview.take', $attempt->id)
                ->with('success', 'تم بدء تجربة الاختبار (وضع معاينة)');
        } catch (\Throwable $e) {
            return redirect()
                ->route('quizzes.show', $quiz->id)
                ->withErrors(['error' => 'حدث خطأ أثناء بدء تجربة الاختبار: '.$e->getMessage()]);
        }
    }

    public function take(Request $request, $attemptId)
    {
        $attempt = $this->resolvePreviewAttempt($attemptId);

        if ($attempt->status !== 'in_progress') {
            return redirect()
                ->route('quizzes.preview.review', $attempt->id)
                ->with('info', 'تم تسليم محاولة المعاينة بالفعل');
        }

        $takeData = $this->previewFlow->loadTakeData($attempt);

        if (! empty($takeData['timedOut'])) {
            return redirect()
                ->route('quizzes.preview.review', $attempt->id)
                ->with('warning', 'انتهى وقت الاختبار وتم تسليمه تلقائياً');
        }

        return view('admin.pages.quizzes.preview.take', [
            'attempt' => $attempt,
            'questions' => $takeData['questions'],
            'remainingTime' => $takeData['remainingTime'],
            'quiz' => $attempt->quiz,
        ]);
    }

    public function saveAnswer(Request $request, $attemptId)
    {
        $attempt = $this->resolvePreviewAttempt($attemptId);

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حفظ الإجابة، المحاولة غير نشطة',
            ], 400);
        }

        try {
            $this->previewFlow->saveAnswer($attempt, $request);

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الإجابة بنجاح',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الإجابة',
            ], 500);
        }
    }

    public function submit(Request $request, $attemptId)
    {
        $attempt = $this->resolvePreviewAttempt($attemptId);

        if ($attempt->status !== 'in_progress') {
            return redirect()
                ->route('quizzes.preview.review', $attempt->id)
                ->withErrors(['error' => 'هذه المحاولة قد تم تسليمها بالفعل']);
        }

        $answers = [];
        if ($request->has('answers') && is_array($request->answers)) {
            foreach ($request->answers as $questionId => $answerJson) {
                $decoded = json_decode($answerJson, true);
                $answers[$questionId] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answerJson;
            }
        }

        try {
            $this->previewFlow->submitPreviewAttempt($attempt, $answers);

            return redirect()
                ->route('quizzes.preview.review', $attempt->id)
                ->with('success', 'تم تسليم تجربة الاختبار بنجاح');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء تسليم الاختبار: '.$e->getMessage()]);
        }
    }

    public function review($attemptId)
    {
        $attempt = $this->resolvePreviewAttempt($attemptId);

        if ($attempt->status === 'in_progress') {
            return redirect()
                ->route('quizzes.preview.take', $attempt->id)
                ->with('info', 'أكمل تجربة الاختبار أولاً');
        }

        $reviewData = $this->previewFlow->loadReviewData($attempt);

        return view('admin.pages.quizzes.preview.review', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'orderedResponses' => $reviewData['orderedResponses'],
            'stats' => $reviewData['stats'],
        ]);
    }

    protected function resolvePreviewAttempt($attemptId): QuizAttempt
    {
        $attempt = QuizAttempt::with(['quiz.settings', 'quiz.course'])->findOrFail($attemptId);

        if (! $attempt->isPreview()) {
            abort(403, 'هذه ليست محاولة معاينة');
        }

        if ((int) $attempt->student_id !== (int) auth()->id()) {
            abort(403, 'غير مصرح لك بالوصول إلى محاولة المعاينة هذه');
        }

        return $attempt;
    }
}
