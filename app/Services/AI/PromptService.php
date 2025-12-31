<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\File;

class PromptService
{
    protected string $promptsPath;

    public function __construct()
    {
        $this->promptsPath = config('ai.prompts_path', resource_path('prompts'));
    }

    /**
     * Get question generation prompt
     */
    public function getQuestionGenerationPrompt(
        string $topic,
        string $content,
        int $count,
        string $questionType,
        string $difficulty
    ): string {
        $template = $this->loadTemplate('question_generation');
        
        return str_replace([
            '{count}',
            '{question_type}',
            '{topic}',
            '{difficulty}',
            '{content}',
        ], [
            $count,
            $questionType,
            $topic,
            $difficulty,
            $content,
        ], $template);
    }

    /**
     * Get quiz generation prompt
     */
    public function getQuizGenerationPrompt(
        int $totalQuestions,
        array $questionTypes,
        string $difficulty,
        string $topic,
        string $content,
        int $timeLimit
    ): string {
        $template = $this->loadTemplate('quiz_generation');
        
        return str_replace([
            '{total_questions}',
            '{question_types}',
            '{difficulty}',
            '{topic}',
            '{content}',
            '{time_limit}',
        ], [
            $totalQuestions,
            implode(', ', $questionTypes),
            $difficulty,
            $topic,
            $content,
            $timeLimit,
        ], $template);
    }

    /**
     * Get essay grading prompt
     */
    public function getEssayGradingPrompt(
        string $questionText,
        array $criteria,
        string $studentAnswer,
        float $maxScore
    ): string {
        $template = $this->loadTemplate('essay_grading');
        
        $criteriaText = $this->formatCriteria($criteria);
        
        return str_replace([
            '{question_text}',
            '{criteria}',
            '{student_answer}',
            '{max_score}',
        ], [
            $questionText,
            $criteriaText,
            $studentAnswer,
            $maxScore,
        ], $template);
    }

    /**
     * Get content creation prompt
     */
    public function getContentCreationPrompt(
        string $contentType,
        string $topic,
        string $level,
        string $audience,
        string $length
    ): string {
        $template = $this->loadTemplate('content_creation');
        
        return str_replace([
            '{content_type}',
            '{topic}',
            '{level}',
            '{audience}',
            '{length}',
        ], [
            $contentType,
            $topic,
            $level,
            $audience,
            $length,
        ], $template);
    }

    /**
     * Get translation prompt
     */
    public function getTranslationPrompt(
        string $content,
        string $sourceLanguage,
        string $targetLanguage
    ): string {
        $template = $this->loadTemplate('translation');
        
        return str_replace([
            '{content}',
            '{source_language}',
            '{target_language}',
        ], [
            $content,
            $sourceLanguage,
            $targetLanguage,
        ], $template);
    }

    /**
     * Load template file
     */
    protected function loadTemplate(string $templateName): string
    {
        $filename = config("ai.prompts.{$templateName}", "{$templateName}.txt");
        $filePath = $this->promptsPath . '/' . $filename;

        if (File::exists($filePath)) {
            return File::get($filePath);
        }

        // Return default template if file doesn't exist
        return $this->getDefaultTemplate($templateName);
    }

    /**
     * Format criteria for prompt
     */
    protected function formatCriteria(array $criteria): string
    {
        $text = "معايير التصحيح:\n\n";
        
        foreach ($criteria as $key => $criterion) {
            $text .= "- {$key}: {$criterion['description']} (الوزن: {$criterion['weight']}%)\n";
        }

        return $text;
    }

    /**
     * Get default template if file doesn't exist
     */
    protected function getDefaultTemplate(string $templateName): string
    {
        return match ($templateName) {
            'question_generation' => 'قم بإنشاء {count} سؤال من نوع {question_type} حول {topic}',
            'quiz_generation' => 'قم بإنشاء اختبار يحتوي على {total_questions} سؤال حول {topic}',
            'essay_grading' => 'قم بتصحيح الإجابة المقالية التالية بناءً على المعايير المحددة',
            default => 'يرجى تنفيذ المهمة المطلوبة',
        };
    }
}

