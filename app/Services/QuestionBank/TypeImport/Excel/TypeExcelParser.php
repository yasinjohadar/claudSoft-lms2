<?php

namespace App\Services\QuestionBank\TypeImport\Excel;

use App\Models\QuestionType;
use App\Services\QuestionBank\TypeImport\CanonicalImportRow;
use App\Services\QuestionBank\TypeImport\TypeImportColumnRegistry;
use App\Services\QuestionBankExcelImportService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TypeExcelParser
{
    public function __construct(
        private readonly QuestionBankExcelImportService $excel = new QuestionBankExcelImportService
    ) {}

    /**
     * @return list<CanonicalImportRow>
     */
    public function parse(string $filePath, QuestionType $questionType): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $this->excel->getQuestionsWorksheet($spreadsheet);
        $rows = $worksheet->toArray();
        $headerRow = array_shift($rows) ?? [];
        $headers = TypeImportColumnRegistry::headersForType($questionType->name);
        $typeLabel = $questionType->display_name;

        $parsed = [];
        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2;

            if (empty(array_filter($row, fn ($c) => trim((string) ($c ?? '')) !== ''))) {
                continue;
            }

            $values = [];
            foreach ($headerRow as $colIndex => $headerCell) {
                $label = trim((string) ($headerCell ?? ''));
                if ($label === '' || ! isset($headers[$label])) {
                    continue;
                }
                $values[$headers[$label]] = trim((string) ($row[$colIndex] ?? ''));
            }

            if (($values['points'] ?? '') === '') {
                $values['points'] = '1';
            }
            if (($values['difficulty'] ?? '') === '') {
                $values['difficulty'] = 'medium';
            }

            $parsed[] = CanonicalImportRow::fromValues($rowNumber, $typeLabel, $values);
        }

        return $parsed;
    }
}
