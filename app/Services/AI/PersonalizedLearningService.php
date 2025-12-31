<?php

namespace App\Services\AI;

use App\Models\User;
use App\Models\Course;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;

class PersonalizedLearningService
{
    protected AIManager $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Recommend next lesson
     *
     * @param int $studentId
     * @param int $courseId
     * @param string|null $providerName
     * @return array
     */
    public function recommendNextLesson(int $studentId, int $courseId, ?string $providerName = null): array
    {
        $student = User::findOrFail($studentId);
        $course = Course::with('lessons')->findOrFail($courseId);

        // Get student progress
        $completedLessons = $student->completedLessons()->where('course_id', $courseId)->pluck('lesson_id')->toArray();
        $quizScores = $student->quizAttempts()->where('course_id', $courseId)->avg('score_obtained') ?? 0;

        $prompt = "بناءً على تقدم الطالب التالي، اقترح الدرس التالي المناسب:\n\n";
        $prompt .= "الكورس: {$course->title}\n";
        $prompt .= "الدروس المكتملة: " . count($completedLessons) . "\n";
        $prompt .= "متوسط الدرجات: {$quizScores}\n\n";
        $prompt .= "الدروس المتاحة:\n";
        foreach ($course->lessons as $lesson) {
            $status = in_array($lesson->id, $completedLessons) ? 'مكتمل' : 'غير مكتمل';
            $prompt .= "- {$lesson->title} ({$status})\n";
        }
        $prompt .= "\nيرجى اقتراح الدرس التالي المناسب مع شرح السبب";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return [
            'recommendation' => $response['content'],
            'suggested_lesson_id' => $this->extractLessonId($response['content'], $course->lessons),
        ];
    }

    /**
     * Generate personalized quiz
     *
     * @param int $studentId
     * @param array $weakAreas
     * @param string|null $providerName
     * @return array
     */
    public function generatePersonalizedQuiz(int $studentId, array $weakAreas, ?string $providerName = null): array
    {
        $student = User::findOrFail($studentId);

        $prompt = "قم بإنشاء اختبار مخصص للطالب بناءً على المناطق الضعيفة التالية:\n\n";
        $prompt .= "المناطق الضعيفة:\n";
        foreach ($weakAreas as $area) {
            $prompt .= "- {$area}\n";
        }
        $prompt .= "\nيرجى إنشاء اختبار يركز على تحسين هذه المناطق";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return [
            'quiz_suggestions' => $response['content'],
            'focus_areas' => $weakAreas,
        ];
    }

    /**
     * Create study plan
     *
     * @param int $studentId
     * @param array $goals
     * @param string|null $providerName
     * @return array
     */
    public function createStudyPlan(int $studentId, array $goals, ?string $providerName = null): array
    {
        $student = User::findOrFail($studentId);

        $prompt = "قم بإنشاء خطة دراسية مخصصة للطالب بناءً على الأهداف التالية:\n\n";
        $prompt .= "الأهداف:\n";
        foreach ($goals as $goal) {
            $prompt .= "- {$goal}\n";
        }
        $prompt .= "\nيرجى إنشاء خطة دراسية تشمل:\n";
        $prompt .= "1. الجدول الزمني\n";
        $prompt .= "2. المواضيع المطلوبة\n";
        $prompt .= "3. المهام والأنشطة\n";
        $prompt .= "4. نقاط المراجعة\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return [
            'study_plan' => $response['content'],
            'goals' => $goals,
        ];
    }

    /**
     * Extract lesson ID from recommendation
     */
    protected function extractLessonId(string $content, $lessons): ?int
    {
        foreach ($lessons as $lesson) {
            if (stripos($content, $lesson->title) !== false) {
                return $lesson->id;
            }
        }
        return null;
    }
}

