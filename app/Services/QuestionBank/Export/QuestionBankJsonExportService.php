<?php

namespace App\Services\QuestionBank\Export;

use App\Models\QuestionType;
use Illuminate\Support\Collection;

class QuestionBankJsonExportService
{
    public function __construct(
        private readonly QuestionBankExportSerializer $serializer = new QuestionBankExportSerializer
    ) {}

    /**
     * @param  Collection<int, \App\Models\QuestionBank>  $questions
     */
    public function exportForType(Collection $questions, QuestionType $questionType): string
    {
        $structured = [];
        foreach ($questions as $question) {
            $structured[] = $this->serializer->toStructuredQuestion($question);
        }

        $payload = [
            'version' => '1.0',
            'question_type' => $questionType->name,
            'questions' => $structured,
        ];

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }
}
