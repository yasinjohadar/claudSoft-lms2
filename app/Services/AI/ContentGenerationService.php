<?php

namespace App\Services\AI;

use App\Models\Course;
use App\Models\Lesson;
use App\Services\AI\AIManager;
use App\Services\AI\PromptService;
use Illuminate\Support\Facades\Log;

class ContentGenerationService
{
    protected AIManager $aiManager;
    protected PromptService $promptService;

    public function __construct(AIManager $aiManager, PromptService $promptService)
    {
        $this->aiManager = $aiManager;
        $this->promptService = $promptService;
    }

    /**
     * Generate course description
     *
     * @param int $courseId
     * @param string|null $providerName
     * @return string
     */
    public function generateCourseDescription(int $courseId, ?string $providerName = null): string
    {
        $course = Course::findOrFail($courseId);

        $prompt = "قم بإنشاء وصف شامل ومفصل للكورس التالي:\n\n";
        $prompt .= "اسم الكورس: {$course->title}\n";
        $prompt .= "الوصف الحالي: " . ($course->description ?? '') . "\n\n";
        $prompt .= "يرجى إنشاء وصف جذاب ومفصل يوضح:\n";
        $prompt .= "1. محتوى الكورس\n";
        $prompt .= "2. الفوائد والمهارات المكتسبة\n";
        $prompt .= "3. الجمهور المستهدف\n";
        $prompt .= "4. المتطلبات السابقة (إن وجدت)\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return $response['content'];
    }

    /**
     * Generate lesson summary
     *
     * @param int $lessonId
     * @param string|null $providerName
     * @return string
     */
    public function generateLessonSummary(int $lessonId, ?string $providerName = null): string
    {
        $lesson = Lesson::with('course')->findOrFail($lessonId);

        $prompt = "قم بإنشاء ملخص شامل للدرس التالي:\n\n";
        $prompt .= "عنوان الدرس: {$lesson->title}\n";
        $prompt .= "محتوى الدرس:\n{$lesson->content}\n\n";
        $prompt .= "يرجى إنشاء ملخص يوضح:\n";
        $prompt .= "1. النقاط الرئيسية\n";
        $prompt .= "2. المفاهيم المهمة\n";
        $prompt .= "3. الخلاصة\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return $response['content'];
    }

    /**
     * Generate study notes
     *
     * @param string $content
     * @param string|null $providerName
     * @return string
     */
    public function generateStudyNotes(string $content, ?string $providerName = null): string
    {
        $prompt = "قم بإنشاء ملاحظات دراسية منظمة من المحتوى التالي:\n\n";
        $prompt .= "{$content}\n\n";
        $prompt .= "يرجى تنظيم الملاحظات بشكل:\n";
        $prompt .= "1. نقاط رئيسية\n";
        $prompt .= "2. مفاهيم مهمة\n";
        $prompt .= "3. أمثلة توضيحية\n";
        $prompt .= "4. أسئلة للمراجعة\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return $response['content'];
    }

    /**
     * Translate content
     *
     * @param string $content
     * @param string $targetLanguage
     * @param string|null $providerName
     * @return string
     */
    public function translateContent(string $content, string $targetLanguage, ?string $providerName = null): string
    {
        $sourceLanguage = 'العربية';
        
        $prompt = $this->promptService->getTranslationPrompt(
            $content,
            $sourceLanguage,
            $targetLanguage
        );

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        // Try to extract translated content from JSON if present
        $jsonStart = strpos($response['content'], '{');
        $jsonEnd = strrpos($response['content'], '}');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $json = substr($response['content'], $jsonStart, $jsonEnd - $jsonStart + 1);
            $data = json_decode($json, true);
            
            if (isset($data['translated_content'])) {
                return $data['translated_content'];
            }
        }

        return $response['content'];
    }
}

