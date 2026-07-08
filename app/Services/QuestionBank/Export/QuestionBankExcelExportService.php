<?php

namespace App\Services\QuestionBank\Export;

use App\Models\QuestionType;
use App\Services\QuestionBank\TypeImport\TypeImportColumnRegistry;
use App\Services\QuestionBankExcelImportService;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class QuestionBankExcelExportService
{
    public function __construct(
        private readonly QuestionBankExportSerializer $serializer = new QuestionBankExportSerializer
    ) {}

    /**
     * @param  Collection<int, \App\Models\QuestionBank>  $questions
     */
    public function exportMultiType(Collection $questions): string
    {
        $headers = QuestionBankExcelImportService::templateHeadersOrder();
        $keyMap = array_flip(QuestionBankExcelImportService::headerLabelToKeyMap());
        $rows = [];

        foreach ($this->serializer->toImportRows($questions) as $importRow) {
            $line = [];
            foreach ($headers as $label) {
                $key = $keyMap[$label] ?? null;
                $line[] = $key ? ($importRow[$key] ?? '') : '';
            }
            $rows[] = $line;
        }

        return $this->writeSpreadsheet($headers, $rows, 'تصدير بنك الأسئلة');
    }

    /**
     * @param  Collection<int, \App\Models\QuestionBank>  $questions
     */
    public function exportForType(Collection $questions, QuestionType $questionType): string
    {
        $headers = TypeImportColumnRegistry::headersForType($questionType->name);
        $headerLabels = array_keys($headers);
        $rows = [];

        foreach ($this->serializer->toImportRows($questions) as $importRow) {
            $line = [];
            foreach ($headers as $key) {
                $line[] = $importRow[$key] ?? '';
            }
            $rows[] = $line;
        }

        return $this->writeSpreadsheet($headerLabels, $rows, 'تصدير '.$questionType->display_name);
    }

    /**
     * @param  list<string>  $headerLabels
     * @param  list<list<string>>  $rows
     */
    private function writeSpreadsheet(array $headerLabels, array $rows, string $guideTitle): string
    {
        $spreadsheet = new Spreadsheet;
        $guide = $spreadsheet->getActiveSheet();
        $guide->setTitle(QuestionBankExcelImportService::SHEET_GUIDE);
        $guide->fromArray(['ملاحظة', 'التفاصيل'], null, 'A1');
        $guide->fromArray([
            [$guideTitle, 'تم التصدير بصيغة متوافقة مع الاستيراد'],
            ['إعادة الاستيراد', 'ارفع هذا الملف من شاشة الاستيراد نفسها'],
        ], null, 'A2');
        $guide->getStyle('A1:B1')->getFont()->setBold(true);

        $questionsSheet = new Worksheet($spreadsheet, QuestionBankExcelImportService::SHEET_QUESTIONS);
        $spreadsheet->addSheet($questionsSheet, 1);
        $questionsSheet->fromArray($headerLabels, null, 'A1');

        if ($rows !== []) {
            $questionsSheet->fromArray($rows, null, 'A2');
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

        $tempFile = tempnam(sys_get_temp_dir(), 'qb_export');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return $tempFile;
    }
}
