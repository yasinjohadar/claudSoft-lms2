<?php

namespace App\Services\Reports;

use App\Ai\Agents\StudentProgressReportPlainAgent;
use App\Models\LaravelAiModel;
use App\Services\AiNew\LaravelAiPromptRunner;
use App\Services\AiNew\LaravelAiProviderManager;
use App\Services\AiNew\LaravelAiRequestLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class StudentCourseReportNarrativeService
{
    public const OPERATION = 'reports.student_progress_narrative';

    public function __construct(
        private LaravelAiProviderManager $providerManager,
        private LaravelAiPromptRunner $promptRunner,
        private LaravelAiRequestLogger $logger,
    ) {}

    /**
     * @param  array<string, mixed>  $facts
     * @return array{narrative: string, meta: array<string, mixed>}
     */
    public function generate(array $facts, LaravelAiModel $model, ?Authenticatable $actor): array
    {
        set_time_limit(180);

        $json = json_encode($facts, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \InvalidArgumentException('تعذر ترميز بيانات التقرير.');
        }
        $prompt = "البيانات التالية هي الحقائق الرقمية والوصفية المعتمدة فقط. اكتب التقرير بالعربية وفق تعليمات النظام.\n\n{$json}";

        $t0 = microtime(true);
        try {
            $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt) {
                return $this->promptRunner->runPlain($model, new StudentProgressReportPlainAgent, $prompt, 180);
            });

            $latencyMs = (int) round((microtime(true) - $t0) * 1000);
            $narrative = trim((string) $response->text);
            if ($narrative === '') {
                throw new \RuntimeException('لم يُرجع الموديل أي نص.');
            }

            $usage = $response->usage;
            $meta = [
                'latency_ms' => $latencyMs,
                'prompt_tokens' => $usage->promptTokens ?? null,
                'completion_tokens' => $usage->completionTokens ?? null,
            ];

            $this->logger->logSuccess(
                $model,
                $actor,
                self::OPERATION,
                ['facts_keys' => array_keys($facts)],
                ['narrative_preview' => mb_substr($narrative, 0, 500)],
                $latencyMs,
            );

            return [
                'narrative' => $narrative,
                'meta' => $meta,
            ];
        } catch (\Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $t0) * 1000);
            $this->logger->logFailure(
                $model,
                $actor,
                self::OPERATION,
                ['facts_keys' => array_keys($facts)],
                $e->getMessage(),
                $latencyMs,
            );
            throw $e;
        }
    }
}
