<?php

namespace App\Services\QuestionBank\TypeImport\Json;

use App\Models\QuestionType;
use App\Services\QuestionBank\TypeImport\CanonicalImportRow;

class JsonImportParser
{
    public function __construct(
        private readonly StructuredJsonParser $structuredParser = new StructuredJsonParser,
        private readonly FlatJsonParser $flatParser = new FlatJsonParser
    ) {}

    /**
     * @return list<CanonicalImportRow>
     */
    public function parse(string $jsonContent, QuestionType $questionType): array
    {
        $payload = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('ملف JSON غير صالح: '.json_last_error_msg());
        }

        if (! is_array($payload)) {
            throw new \InvalidArgumentException('يجب أن يكون محتوى JSON كائناً أو مصفوفة');
        }

        if ($this->isStructuredPayload($payload)) {
            return $this->structuredParser->parse($payload, $questionType);
        }

        return $this->flatParser->parse($payload, $questionType);
    }

    /**
     * @param  array<string, mixed>|list<mixed>  $payload
     */
    private function isStructuredPayload(array $payload): bool
    {
        if (isset($payload['version']) && isset($payload['questions'])) {
            return true;
        }

        if (isset($payload['questions']) && is_array($payload['questions'])) {
            $first = $payload['questions'][0] ?? null;
            if (is_array($first)) {
                return isset($first['options']) || isset($first['matching_pairs']) || isset($first['items']);
            }
        }

        return false;
    }
}
