<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ProgrammingLanguage;
use App\Models\QuestionType;
use App\Services\QuestionBank\TypeImport\Excel\TypeExcelParser;
use App\Services\QuestionBank\TypeImport\Excel\TypeExcelTemplateGenerator;
use App\Services\QuestionBank\TypeImport\Json\JsonImportParser;
use App\Services\QuestionBank\TypeImport\Json\TypeJsonTemplateGenerator;
use App\Services\QuestionBank\TypeImport\QuestionBankImportPersister;
use App\Services\QuestionBank\TypeImport\TypeImportColumnRegistry;
use App\Services\QuestionBank\TypeImport\TypeImportPreviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuestionBankTypeImportController extends Controller
{
    public function __construct(
        private readonly TypeImportPreviewService $previewService = new TypeImportPreviewService,
        private readonly QuestionBankImportPersister $persister = new QuestionBankImportPersister
    ) {}

    public function selectType(string $format): View|RedirectResponse
    {
        if (! in_array($format, ['excel', 'json'], true)) {
            abort(404);
        }

        $questionTypes = QuestionType::where('is_active', true)
            ->whereIn('name', TypeImportColumnRegistry::supportedTypes())
            ->orderBy('id')
            ->get();

        return view('admin.pages.question-bank.type-import.select-type', compact('format', 'questionTypes'));
    }

    public function showImportForm(string $format, string $type): View
    {
        $questionType = $this->resolveQuestionType($type);
        $programmingLanguages = ProgrammingLanguage::active()->orderBy('sort_order')->get();
        $courses = Course::where('is_published', true)->orderBy('title')->get();

        return view('admin.pages.question-bank.type-import.import', compact('format', 'questionType', 'programmingLanguages', 'courses'));
    }

    public function downloadTemplate(string $format, string $type): BinaryFileResponse
    {
        $questionType = $this->resolveQuestionType($type);

        if ($format === 'excel') {
            $generator = new TypeExcelTemplateGenerator;
            $tempFile = $generator->generate($questionType);
            $filename = 'question-bank-template-'.$questionType->name.'.xlsx';

            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
        }

        $generator = new TypeJsonTemplateGenerator;
        $content = $generator->generate($questionType);
        $tempFile = tempnam(sys_get_temp_dir(), 'qb_json_tpl');
        file_put_contents($tempFile, $content);
        $filename = 'question-bank-template-'.$questionType->name.'.json';

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public function previewImport(Request $request, string $format, string $type): JsonResponse
    {
        $questionType = $this->resolveQuestionType($type);

        $rules = array_merge($format === 'excel'
            ? ['import_file' => 'required|mimes:xlsx,xls|max:10240']
            : ['import_file' => 'required|file|mimes:json,txt|max:10240'], [
                'default_course_id' => 'nullable|exists:courses,id',
                'default_programming_language_id' => 'nullable|exists:programming_languages,id',
            ]);

        $messages = [
            'import_file.required' => 'يرجى اختيار ملف',
            'import_file.mimes' => $format === 'excel'
                ? 'يجب أن يكون الملف بصيغة Excel (.xlsx أو .xls)'
                : 'يجب أن يكون الملف بصيغة JSON',
            'import_file.max' => 'حجم الملف يجب أن يكون أقل من 10 ميجابايت',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('import_file');
            $path = $file->getRealPath();

            if ($format === 'excel') {
                $parser = new TypeExcelParser;
                $rows = $parser->parse($path, $questionType);
            } else {
                $parser = new JsonImportParser;
                $rows = $parser->parse((string) file_get_contents($path), $questionType);
            }

            if ($rows === []) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم العثور على أسئلة في الملف',
                ], 422);
            }

            $preview = $this->previewService->buildPreview(
                $rows,
                $questionType,
                $request->filled('default_course_id') ? (int) $request->input('default_course_id') : null,
                $request->filled('default_programming_language_id') ? (int) $request->input('default_programming_language_id') : null
            );

            return response()->json([
                'success' => true,
                ...$preview,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء قراءة الملف: '.$e->getMessage(),
            ], 500);
        }
    }

    public function processImport(Request $request, string $format, string $type): JsonResponse|RedirectResponse
    {
        $questionType = $this->resolveQuestionType($type);
        $expectsJson = $request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        $validator = Validator::make($request->all(), [
            'questions_data' => 'required|json',
            'default_course_id' => 'nullable|exists:courses,id',
            'default_programming_language_id' => 'nullable|exists:programming_languages,id',
        ], [
            'questions_data.required' => 'بيانات الأسئلة مطلوبة',
            'questions_data.json' => 'بيانات الأسئلة يجب أن تكون بصيغة JSON',
        ]);

        if ($validator->fails()) {
            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطأ في التحقق من البيانات',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $questionsData = json_decode($request->questions_data, true);
        if (! is_array($questionsData)) {
            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                ], 422);
            }

            return back()->withErrors(['error' => 'بيانات غير صحيحة']);
        }

        try {
            DB::beginTransaction();

            $result = $this->previewService->processRows(
                $questionsData,
                $questionType,
                $this->persister,
                $request->filled('default_programming_language_id') ? (int) $request->input('default_programming_language_id') : null,
                $request->filled('default_course_id') ? (int) $request->input('default_course_id') : null
            );

            DB::commit();

            $message = "تم استيراد {$result['imported']} سؤال بنجاح";
            if ($result['skipped'] > 0) {
                $message .= "، تم تخطي {$result['skipped']} سؤال";
            }

            if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    ...$result,
                ]);
            }

            return redirect()->route('question-bank.index')
                ->with('success', $message)
                ->with('import_errors', $result['errors']);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء الاستيراد: '.$e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => 'حدث خطأ أثناء الاستيراد: '.$e->getMessage()]);
        }
    }

    private function resolveQuestionType(string $type): QuestionType
    {
        if (! TypeImportColumnRegistry::isSupported($type)) {
            abort(404);
        }

        $questionType = QuestionType::where('name', $type)->where('is_active', true)->first();

        if (! $questionType) {
            abort(404);
        }

        return $questionType;
    }
}
