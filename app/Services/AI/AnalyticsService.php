<?php

namespace App\Services\AI;

use App\Models\User;
use App\Models\Course;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    protected AIManager $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Analyze student performance
     *
     * @param int $studentId
     * @param string|null $providerName
     * @return array
     */
    public function analyzeStudentPerformance(int $studentId, ?string $providerName = null): array
    {
        $student = User::findOrFail($studentId);

        // Get student data
        $quizAttempts = $student->quizAttempts()->with('quiz')->get();
        $completedLessons = $student->completedLessons()->count();
        $averageScore = $quizAttempts->avg('score_obtained') ?? 0;

        $prompt = "قم بتحليل أداء الطالب التالي:\n\n";
        $prompt .= "عدد الاختبارات المكتملة: " . $quizAttempts->count() . "\n";
        $prompt .= "الدروس المكتملة: {$completedLessons}\n";
        $prompt .= "متوسط الدرجات: {$averageScore}\n\n";
        $prompt .= "نتائج الاختبارات:\n";
        foreach ($quizAttempts->take(10) as $attempt) {
            $prompt .= "- {$attempt->quiz->title ?? 'اختبار'}: {$attempt->score_obtained}/{$attempt->max_score}\n";
        }
        $prompt .= "\nيرجى تقديم:\n";
        $prompt .= "1. تحليل شامل للأداء\n";
        $prompt .= "2. نقاط القوة\n";
        $prompt .= "3. المناطق التي تحتاج تحسين\n";
        $prompt .= "4. توصيات للتحسين\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return [
            'analysis' => $response['content'],
            'metrics' => [
                'completed_lessons' => $completedLessons,
                'average_score' => $averageScore,
                'quiz_count' => $quizAttempts->count(),
            ],
        ];
    }

    /**
     * Identify learning gaps
     *
     * @param int $studentId
     * @param int $courseId
     * @param string|null $providerName
     * @return array
     */
    public function identifyLearningGaps(int $studentId, int $courseId, ?string $providerName = null): array
    {
        $student = User::findOrFail($studentId);
        $course = Course::findOrFail($courseId);

        // Get student performance by topic
        $weakAreas = $student->quizResponses()
            ->whereHas('question', function($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->where('is_correct', false)
            ->with('question')
            ->get()
            ->groupBy(function($response) {
                return $response->question->metadata['topic'] ?? 'عام';
            })
            ->map(function($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(5)
            ->keys()
            ->toArray();

        $prompt = "قم بتحديد الفجوات التعليمية للطالب في الكورس التالي:\n\n";
        $prompt .= "الكورس: {$course->title}\n";
        $prompt .= "المناطق الضعيفة:\n";
        foreach ($weakAreas as $area) {
            $prompt .= "- {$area}\n";
        }
        $prompt .= "\nيرجى تقديم:\n";
        $prompt .= "1. تحليل الفجوات\n";
        $prompt .= "2. أسباب محتملة\n";
        $prompt .= "3. خطة لسد الفجوات\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return [
            'gaps_analysis' => $response['content'],
            'weak_areas' => $weakAreas,
        ];
    }

    /**
     * Predict student success
     *
     * @param int $studentId
     * @param int $courseId
     * @param string|null $providerName
     * @return array
     */
    public function predictStudentSuccess(int $studentId, int $courseId, ?string $providerName = null): array
    {
        $student = User::findOrFail($studentId);
        $course = Course::findOrFail($courseId);

        // Get student metrics
        $progress = $student->completedLessons()->where('course_id', $courseId)->count();
        $totalLessons = $course->lessons()->count();
        $progressPercentage = $totalLessons > 0 ? ($progress / $totalLessons) * 100 : 0;
        
        $averageScore = $student->quizAttempts()
            ->where('course_id', $courseId)
            ->avg('score_obtained') ?? 0;

        $prompt = "قم بالتنبؤ باحتمالية نجاح الطالب في الكورس بناءً على:\n\n";
        $prompt .= "الكورس: {$course->title}\n";
        $prompt .= "التقدم: {$progressPercentage}%\n";
        $prompt .= "متوسط الدرجات: {$averageScore}\n";
        $prompt .= "عدد الاختبارات: " . $student->quizAttempts()->where('course_id', $courseId)->count() . "\n\n";
        $prompt .= "يرجى تقديم:\n";
        $prompt .= "1. احتمالية النجاح\n";
        $prompt .= "2. العوامل المؤثرة\n";
        $prompt .= "3. التوصيات لتحسين النجاح\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return [
            'prediction' => $response['content'],
            'success_probability' => $this->calculateSuccessProbability($progressPercentage, $averageScore),
            'factors' => [
                'progress' => $progressPercentage,
                'average_score' => $averageScore,
            ],
        ];
    }

    /**
     * Calculate success probability
     */
    protected function calculateSuccessProbability(float $progressPercentage, float $averageScore): float
    {
        // Simple calculation: weighted average
        $progressWeight = 0.3;
        $scoreWeight = 0.7;
        
        $progressScore = min($progressPercentage / 100, 1.0);
        $scoreNormalized = min($averageScore / 100, 1.0);
        
        return ($progressScore * $progressWeight) + ($scoreNormalized * $scoreWeight);
    }
}

