<?php

namespace App\Services\QuestionBank\TypeImport\Excel;

use App\Models\QuestionType;
use App\Services\QuestionBank\TypeImport\TypeImportColumnRegistry;
use App\Services\QuestionBankExcelImportService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TypeExcelTemplateGenerator
{
    public function generate(QuestionType $questionType): string
    {
        $typeName = $questionType->name;
        $headers = TypeImportColumnRegistry::headersForType($typeName);
        $headerLabels = array_keys($headers);

        $spreadsheet = new Spreadsheet;
        $guide = $spreadsheet->getActiveSheet();
        $guide->setTitle(QuestionBankExcelImportService::SHEET_GUIDE);
        $guide->fromArray(['العمود', 'الوصف', 'ملاحظات'], null, 'A1');
        $guideRows = TypeImportColumnRegistry::guideRowsForType($typeName);
        $guide->fromArray($guideRows, null, 'A2');
        $guide->getStyle('A1:C1')->getFont()->setBold(true);

        $questionsSheet = new Worksheet($spreadsheet, QuestionBankExcelImportService::SHEET_QUESTIONS);
        $spreadsheet->addSheet($questionsSheet, 1);
        $questionsSheet->fromArray($headerLabels, null, 'A1');

        $examples = TypeImportColumnRegistry::exampleRowsForType($typeName);
        $exampleMatrix = [];
        foreach ($examples as $example) {
            $row = [];
            foreach ($headers as $key) {
                $row[] = $example[$key] ?? '';
            }
            $exampleMatrix[] = $row;
        }
        if ($exampleMatrix !== []) {
            $questionsSheet->fromArray($exampleMatrix, null, 'A2');
        }

        $lastCol = Coordinate::stringFromColumnIndex(count($headerLabels));
        $questionsSheet->getStyle('A1:'.$lastCol.'1')->getFont()->setBold(true);
        $questionsSheet->getStyle('A1:'.$lastCol.'1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $questionsSheet->getStyle('A1:'.$lastCol.'1')->getFont()->getColor()->setARGB('FFFFFFFF');

        for ($ci = 1; $ci <= count($headerLabels); $ci++) {
            $questionsSheet->getColumnDimensionByColumn($ci)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(1);

        $tempFile = tempnam(sys_get_temp_dir(), 'qb_type_tpl');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return $tempFile;
    }
}
