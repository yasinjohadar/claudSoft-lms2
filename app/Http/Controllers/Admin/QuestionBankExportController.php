<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use App\Models\QuestionType;
use App\Services\QuestionBank\Export\QuestionBankExcelExportService;
use App\Services\QuestionBank\Export\QuestionBankJsonExportService;
use App\Services\QuestionBank\TypeImport\TypeImportColumnRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionBankExportController extends Controller
{
    public function __construct(
        private readonly QuestionBankExcelExportService $excelExport,
        private readonly QuestionBankJsonExportService $jsonExport,
    ) {}

    public function selectType(string $format): View
    {
        if (! in_array($format, ['excel', 'json'], true)) {
            abort(404);
        }

        $questionTypes = QuestionType::where('is_active', true)
            ->whereIn('name', TypeImportColumnRegistry::supportedTypes())
            ->orderBy('id')
            ->get();

        $filterQuery = request()->only([
            'search', 'course_id', 'question_type_id', 'difficulty', 'language_id', 'question_ids',
        ]);

        return view('admin.pages.question-bank.type-export.select-type', compact('format', 'questionTypes', 'filterQuery'));
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $questions = $this->buildExportQuery($request)->get();

        if ($questions->isEmpty()) {
            return back()->withErrors(['error' => 'لا توجد أسئلة للتصدير وفق الفلاتر المحددة.']);
        }

        $tempFile = $this->excelExport->exportMultiType($questions);
        $filename = 'question-bank-export-'.date('Y-m-d_His').'.xlsx';

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public function exportByType(Request $request, string $format, string $type): BinaryFileResponse|StreamedResponse|RedirectResponse
    {
        if (! in_array($format, ['excel', 'json'], true)) {
            abort(404);
        }

        $questionType = $this->resolveQuestionType($type);
        $questions = $this->buildExportQuery($request)
            ->where('question_type_id', $questionType->id)
            ->get();

        if ($questions->isEmpty()) {
            return back()->withErrors(['error' => 'لا توجد أسئلة من هذا النوع للتصدير وفق الفلاتر المحددة.']);
        }

        if ($format === 'excel') {
            $tempFile = $this->excelExport->exportForType($questions, $questionType);
            $filename = 'question-bank-'.$questionType->name.'-'.date('Y-m-d_His').'.xlsx';

            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
        }

        $content = $this->jsonExport->exportForType($questions, $questionType);
        $filename = 'question-bank-'.$questionType->name.'-'.date('Y-m-d_His').'.json';

        return response()->streamDownload(
            static function () use ($content) {
                echo $content;
            },
            $filename,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }

    /**
     * @return Builder<QuestionBank>
     */
    private function buildExportQuery(Request $request): Builder
    {
        $query = QuestionBank::with(['questionType', 'course', 'options', 'programmingLanguages'])
            ->orderBy('id');

        if ($request->filled('question_ids')) {
            $ids = is_array($request->input('question_ids'))
                ? $request->input('question_ids')
                : explode(',', (string) $request->input('question_ids'));

            $query->whereIn('id', array_filter(array_map('intval', $ids)));

            return $query;
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('question_type_id')) {
            $query->where('question_type_id', $request->question_type_id);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty_level', $request->difficulty);
        }

        if ($request->filled('language_id')) {
            $query->whereHas('programmingLanguages', function ($q) use ($request) {
                $q->where('programming_languages.id', $request->language_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question_text', 'like', '%'.$search.'%')
                    ->orWhere('explanation', 'like', '%'.$search.'%');
            });
        }

        return $query;
    }

    private function resolveQuestionType(string $type): QuestionType
    {
        $questionType = QuestionType::where('name', $type)
            ->orWhere('display_name', $type)
            ->first();

        if (! $questionType || ! TypeImportColumnRegistry::isSupported($questionType->name)) {
            abort(404, 'نوع السؤال غير مدعوم للتصدير');
        }

        return $questionType;
    }
}
