<?php

namespace App\Services\AI;

use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;

class StudentSupportService
{
    protected AIManager $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Answer student question
     *
     * @param string $question
     * @param string|null $context
     * @param string|null $providerName
     * @return string
     */
    public function answerStudentQuestion(string $question, ?string $context = null, ?string $providerName = null): string
    {
        $prompt = "أنت مساعد تعليمي ذكي. أجب على سؤال الطالب التالي بشكل واضح ومفيد:\n\n";
        $prompt .= "السؤال: {$question}\n\n";
        
        if ($context) {
            $prompt .= "السياق:\n{$context}\n\n";
        }
        
        $prompt .= "يرجى تقديم إجابة:\n";
        $prompt .= "1. واضحة ومباشرة\n";
        $prompt .= "2. مفيدة تعليمياً\n";
        $prompt .= "3. مع أمثلة إذا لزم الأمر\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return $response['content'];
    }

    /**
     * Explain concept
     *
     * @param string $concept
     * @param string $level
     * @param string|null $providerName
     * @return string
     */
    public function explainConcept(string $concept, string $level = 'beginner', ?string $providerName = null): string
    {
        $levelText = match($level) {
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
            default => 'عام',
        };

        $prompt = "قم بشرح المفهوم التالي بشكل مناسب للمستوى ({$levelText}):\n\n";
        $prompt .= "المفهوم: {$concept}\n\n";
        $prompt .= "يرجى تقديم شرح:\n";
        $prompt .= "1. واضح ومناسب للمستوى\n";
        $prompt .= "2. مع أمثلة عملية\n";
        $prompt .= "3. مع تطبيقات واقعية\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return $response['content'];
    }

    /**
     * Provide hints
     *
     * @param int $questionId
     * @param array $studentProgress
     * @param string|null $providerName
     * @return string
     */
    public function provideHints(int $questionId, array $studentProgress, ?string $providerName = null): string
    {
        $question = \App\Models\QuestionBank::findOrFail($questionId);

        $prompt = "قم بتقديم تلميحات مفيدة للطالب حول السؤال التالي:\n\n";
        $prompt .= "السؤال: {$question->question_text}\n\n";
        
        if (!empty($studentProgress)) {
            $prompt .= "تقدم الطالب:\n";
            foreach ($studentProgress as $key => $value) {
                $prompt .= "- {$key}: {$value}\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "يرجى تقديم تلميحات:\n";
        $prompt .= "1. تدريجية (من الأسهل إلى الأصعب)\n";
        $prompt .= "2. لا تكشف الإجابة مباشرة\n";
        $prompt .= "3. تساعد الطالب على التفكير\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return $response['content'];
    }
}

