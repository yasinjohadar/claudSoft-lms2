<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AIStudentFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentFeedbackController extends Controller
{
    /**
     * عرض ملاحظات الطالب
     */
    public function index()
    {
        $feedbacks = AIStudentFeedback::where('student_id', Auth::id())
            ->with(['quizAttempt.quiz', 'aiModel'])
            ->latest()
            ->paginate(15);

        return view('student.feedback.index', compact('feedbacks'));
    }

    /**
     * عرض ملاحظة واحدة
     */
    public function show(AIStudentFeedback $feedback)
    {
        // التأكد من أن الملاحظة تخص الطالب الحالي
        if ($feedback->student_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الملاحظة');
        }

        $feedback->load(['quizAttempt.quiz', 'aiModel']);

        return view('student.feedback.show', compact('feedback'));
    }
}


