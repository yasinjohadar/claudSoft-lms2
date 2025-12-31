<?php

namespace App\Services\Ai;

use App\Models\ContentSummary;
use App\Models\AIModel;
use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Support\Facades\Log;

class AIContentSummaryService
{
    public function __construct(
        private AIModelService $modelService,
        private AIPromptService $promptService
    ) {}

    /**
     * تلخيص محتوى عام
     */
    public function summarize(string $content, string $type = 'short', ?AIModel $model = null): ContentSummary
    {
        // زيادة وقت التنفيذ إلى 3 دقائق للطلبات الطويلة
        set_time_limit(180);
        
        if (!$model) {
            $model = $this->modelService->getBestModelFor('question_solving');
        }

        if (!$model) {
            throw new \Exception('لا يوجد موديل AI متاح للتلخيص');
        }

        try {
            $prompt = $this->promptService->getContentSummaryPrompt($content, $type);
            
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $model->max_tokens,
                'temperature' => 0.5,
            ]);

            $tokensUsed = $provider->estimateTokens($prompt . $response);
            $cost = $model->getCost($tokensUsed);

            $summary = ContentSummary::create([
                'summarizable_type' => 'manual',
                'summarizable_id' => 0,
                'summary_text' => $response,
                'summary_type' => $type,
                'ai_model_id' => $model->id,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
                'created_by' => auth()->id(),
            ]);

            return $summary;
        } catch (\Exception $e) {
            Log::error('Error summarizing content: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * تلخيص درس
     */
    public function summarizeLesson(Lesson $lesson, string $type = 'short', ?AIModel $model = null): ContentSummary
    {
        $content = $lesson->content ?? '';
        if (empty($content)) {
            throw new \Exception('الدرس لا يحتوي على محتوى للتلخيص');
        }

        $summary = $this->summarize($content, $type, $model);
        $summary->summarizable_type = Lesson::class;
        $summary->summarizable_id = $lesson->id;
        $summary->save();

        return $summary;
    }

    /**
     * تلخيص كورس
     */
    public function summarizeCourse(Course $course, string $type = 'short', ?AIModel $model = null): ContentSummary
    {
        // جمع محتوى الدروس في الكورس
        $lessons = collect();
        foreach ($course->sections as $section) {
            foreach ($section->modules as $module) {
                if ($module->modulable_type === Lesson::class) {
                    $lessons->push($module->modulable);
                }
            }
        }

        $content = $lessons->map(function ($lesson) {
            return ($lesson->title ?? '') . "\n\n" . ($lesson->description ?? '');
        })->join("\n\n---\n\n");

        if (empty($content)) {
            throw new \Exception('الكورس لا يحتوي على محتوى للتلخيص');
        }

        $summary = $this->summarize($content, $type, $model);
        $summary->summarizable_type = Course::class;
        $summary->summarizable_id = $course->id;
        $summary->save();

        return $summary;
    }
}

