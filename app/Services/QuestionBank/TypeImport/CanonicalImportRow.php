<?php

namespace App\Services\QuestionBank\TypeImport;

use App\Services\QuestionBankExcelImportService;

class CanonicalImportRow
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly array $data
    ) {}

    public static function empty(int $rowNumber, string $questionTypeLabel): self
    {
        $row = QuestionBankExcelImportService::emptyQuestionDataRow($rowNumber);
        $row['question_type'] = $questionTypeLabel;

        return new self($row);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fromValues(int $rowNumber, string $questionTypeLabel, array $values): self
    {
        $row = self::empty($rowNumber, $questionTypeLabel)->data;

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $row)) {
                if (is_array($value)) {
                    $row[$key] = $value;
                } else {
                    $row[$key] = trim((string) $value);
                }
            }
        }

        return new self($row);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
