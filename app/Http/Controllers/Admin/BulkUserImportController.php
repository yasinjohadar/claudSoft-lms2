<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulkImportSession;
use App\Services\BulkImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BulkUserImportController extends Controller
{
    protected $importService;

    public function __construct(BulkImportService $importService)
    {
        $this->middleware('auth');
        $this->middleware(['auth', 'role:admin']);
        $this->importService = $importService;
    }

    /**
     * عرض صفحة رفع الملف
     */
    public function index()
    {
        return view('admin.pages.users.bulk-import.index');
    }

    /**
     * عرض قائمة جميع تقارير الرفع
     */
    public function reports(Request $request)
    {
        $query = BulkImportSession::with('uploadedBy')
            ->orderBy('created_at', 'desc');

        // فلترة حسب الحالة
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // فلترة حسب التاريخ
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // البحث
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhereHas('uploadedBy', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $sessions = $query->paginate(20);

        // إحصائيات عامة
        $stats = [
            'total' => BulkImportSession::count(),
            'completed' => BulkImportSession::where('status', 'completed')->count(),
            'failed' => BulkImportSession::where('status', 'failed')->count(),
            'processing' => BulkImportSession::where('status', 'processing')->count(),
            'pending' => BulkImportSession::where('status', 'pending')->count(),
        ];

        return view('admin.pages.users.bulk-import.reports', [
            'sessions' => $sessions,
            'stats' => $stats,
        ]);
    }

    /**
     * رفع الملف والانتقال لصفحة المعاينة
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
        ], [
            'file.required' => 'يرجى اختيار ملف',
            'file.mimes' => 'الصيغ المدعومة: xlsx, xls, csv',
            'file.max' => 'الحد الأقصى لحجم الملف 10MB',
        ]);

        try {
            $file = $request->file('file');
            
            // تنظيف اسم الملف من المسافات والأحرف الخاصة
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $nameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
            
            // إزالة المسافات والأحرف الخاصة
            $cleanName = preg_replace('/[^a-zA-Z0-9_\x{0600}-\x{06FF}-]/u', '_', $nameWithoutExtension);
            $cleanName = preg_replace('/_+/', '_', $cleanName); // إزالة الشرطات المتعددة
            $cleanName = trim($cleanName, '_'); // إزالة الشرطات من البداية والنهاية
            
            // إذا كان الاسم فارغاً بعد التنظيف، استخدم اسم افتراضي
            if (empty($cleanName)) {
                $cleanName = 'imported_file';
            }
            
            $fileName = time() . '_' . $cleanName . '.' . $extension;
            $filePath = $file->storeAs('imports', $fileName);

            // التحقق من نجاح الحفظ
            if (!$filePath) {
                Log::error('File upload failed', [
                    'original_name' => $originalName,
                    'cleaned_name' => $fileName,
                ]);
                return back()->with('error', 'فشل في حفظ الملف. يرجى المحاولة مرة أخرى.');
            }

            // استخدام Storage للحصول على المسار الكامل
            $fullPath = Storage::path($filePath);

            // التحقق من وجود الملف
            if (!file_exists($fullPath)) {
                Log::error('File not found after upload', [
                    'file_path' => $filePath,
                    'full_path' => $fullPath,
                    'original_name' => $originalName,
                    'cleaned_name' => $fileName,
                ]);
                return back()->with('error', 'فشل في حفظ الملف. يرجى المحاولة مرة أخرى.');
            }

            // قراءة الملف
            $parsedData = $this->importService->parseExcelFile($fullPath);

            // إنشاء جلسة جديدة
            $session = BulkImportSession::create([
                'uploaded_by' => auth()->id(),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'total_rows' => $parsedData['total'],
                'status' => 'pending',
            ]);

            // حفظ البيانات في الـ session
            session([
                'bulk_import_data' => [
                    'session_id' => $session->id,
                    'headers' => $parsedData['headers'],
                    'rows' => $parsedData['rows'],
                    'preview_rows' => array_slice($parsedData['rows'], 0, 5),
                ],
            ]);

            return redirect()->route('users.bulk-import.preview')
                ->with('success', 'تم رفع الملف بنجاح - يرجى مراجعة البيانات');
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error('Excel parsing error', [
                'file_path' => $filePath ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'خطأ في قراءة الملف: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('File upload error', [
                'original_name' => $file->getClientOriginalName() ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'خطأ في رفع الملف: ' . $e->getMessage());
        }
    }

    /**
     * عرض صفحة المعاينة والـ mapping
     */
    public function preview()
    {
        $data = session('bulk_import_data');

        if (!$data) {
            return redirect()->route('users.bulk-import.index')
                ->with('error', 'لا توجد بيانات للمعاينة');
        }

        // تخمين تلقائي للـ mapping
        $suggestedMapping = $this->suggestMapping($data['headers']);

        return view('admin.pages.users.bulk-import.preview', [
            'headers' => $data['headers'],
            'previewRows' => $data['preview_rows'],
            'sessionId' => $data['session_id'],
            'suggestedMapping' => $suggestedMapping,
        ]);
    }

    /**
     * معالجة الملف
     */
    public function process(Request $request)
    {
        // زيادة وقت التنفيذ إلى 5 دقائق (300 ثانية)
        set_time_limit(300);
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'session_id' => 'required|exists:bulk_import_sessions,id',
            'mapping' => 'required|array',
        ]);

        $data = session('bulk_import_data');

        if (!$data) {
            return redirect()->route('users.bulk-import.index')
                ->with('error', 'لا توجد بيانات للمعالجة');
        }

        $session = BulkImportSession::find($request->session_id);
        $mapping = $request->mapping;
        $updateExisting = $request->has('update_existing');
        $skipErrors = $request->has('skip_errors');

        // حفظ الـ mapping
        $session->update(['mapping' => $mapping]);

        // بدء المعالجة
        $session->markAsProcessing();

        try {
            $rows = $data['rows'];
            $totalRows = count($rows);

            // معالجة على دفعات
            $batches = array_chunk($rows, 100);
            $rowNumber = 2; // نبدأ من 2 (بعد الـ header)

            foreach ($batches as $batch) {
                foreach ($batch as $row) {
                    $result = $this->importService->processRow(
                        $row,
                        $mapping,
                        $session,
                        $rowNumber,
                        $updateExisting,
                        $skipErrors
                    );

                    if (!$result['success'] && !$skipErrors) {
                        // إيقاف العملية
                        $session->markAsFailed();
                        return redirect()->route('users.bulk-import.report', $session->id)
                            ->with('error', 'توقفت العملية بسبب خطأ في الصف ' . $rowNumber);
                    }

                    $rowNumber++;
                }

                // تحديث الجلسة كل batch
                $session->refresh();
            }

            // إنهاء العملية
            $session->markAsCompleted();

            // مسح الـ session
            session()->forget('bulk_import_data');

            return redirect()->route('users.bulk-import.report', $session->id)
                ->with('success', 'تم الانتهاء من رفع المستخدمين بنجاح!');
        } catch (\Exception $e) {
            $session->markAsFailed();
            return redirect()->route('users.bulk-import.report', $session->id)
                ->with('error', 'حدث خطأ أثناء المعالجة: ' . $e->getMessage());
        }
    }

    /**
     * عرض التقرير النهائي
     */
    public function report(BulkImportSession $session)
    {
        return view('admin.pages.users.bulk-import.report', [
            'session' => $session,
        ]);
    }

    /**
     * تحميل قالب Excel
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();

        // Sheet 1: التعليمات
        $instructionsSheet = $spreadsheet->getActiveSheet();
        $instructionsSheet->setTitle('التعليمات');

        $instructionsSheet->setCellValue('A1', 'دليل استخدام قالب رفع المستخدمين');
        $instructionsSheet->mergeCells('A1:F1');
        $instructionsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $instructionsSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $instructions = [
            '',
            'الأعمدة المطلوبة:',
            '1. name       : اسم الطالب (مطلوب)',
            '2. name_ar    : الاسم بالعربي (اختياري)',
            '3. email      : البريد الإلكتروني (مطلوب)',
            '4. password   : كلمة المرور (مطلوب)',
            '5. full_phone : رقم الهاتف الكامل بدون صفر (اختياري) - مثال: 966512345678',
            '6. course_name: اسم الكورس (اختياري)',
            '7. group_name : اسم المجموعة (اختياري)',
            '',
            'ملاحظات مهمة:',
            '- البريد الإلكتروني يجب أن يكون فريداً',
            '- رقم الهاتف يكتب كاملاً بدون + أو صفر في البداية',
            '- إذا كان الطالب موجوداً سيتم تحديث بياناته',
            '- اسم الكورس والمجموعة يجب أن يكون مطابقاً تماماً للاسم في النظام',
            '- كلمة المرور سيتم تشفيرها تلقائياً',
        ];

        $row = 2;
        foreach ($instructions as $instruction) {
            $instructionsSheet->setCellValue('A' . $row, $instruction);
            $instructionsSheet->mergeCells('A' . $row . ':G' . $row);
            $row++;
        }

        // تنسيق
        foreach (range('A', 'G') as $col) {
            $instructionsSheet->getColumnDimension($col)->setWidth(25);
        }

        // Sheet 2: البيانات
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('البيانات');

        // Headers
        $headers = ['name', 'name_ar', 'email', 'password', 'full_phone', 'course_name', 'group_name'];
        $headerLabels = ['الاسم', 'الاسم بالعربي', 'البريد الإلكتروني', 'كلمة المرور', 'رقم الهاتف', 'اسم الكورس', 'اسم المجموعة'];

        $col = 'A';
        foreach ($headerLabels as $index => $label) {
            $dataSheet->setCellValue($col . '1', $label);
            $dataSheet->setCellValue($col . '2', $headers[$index]);
            $col++;
        }

        // تنسيق الـ headers
        $dataSheet->getStyle('A1:G2')->getFont()->setBold(true);
        $dataSheet->getStyle('A1:G1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4CAF50');
        $dataSheet->getStyle('A2:G2')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F5E9');

        // أمثلة
        $examples = [
            ['Ahmed Mohammed', 'أحمد محمد', 'ahmad@example.com', '123456', '966512345678', 'Laravel للمبتدئين', 'المجموعة أ'],
            ['Sara Ali', 'سارة علي', 'sara@example.com', 'pass2024', '966501234567', 'PHP المتقدم', 'المجموعة ب'],
            ['Khaled Hassan', 'خالد حسن', 'khaled@example.com', 'secure123', '971501234567', '', ''],
        ];

        $row = 3;
        foreach ($examples as $example) {
            $col = 'A';
            foreach ($example as $value) {
                $dataSheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $dataSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // تعيين الـ sheet النشط للبيانات
        $spreadsheet->setActiveSheetIndex(1);

        // حفظ وتحميل
        $fileName = 'bulk_users_template.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'template');

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * تحميل ملف الأخطاء
     */
    public function downloadErrors(BulkImportSession $session)
    {
        if ($session->failed_rows === 0) {
            return back()->with('error', 'لا توجد أخطاء لتحميلها');
        }

        try {
            $filePath = $this->importService->generateErrorsFile($session);
            $fileName = 'errors_' . $session->id . '.xlsx';

            return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في إنشاء ملف الأخطاء: ' . $e->getMessage());
        }
    }

    /**
     * تخمين الـ mapping بناءً على أسماء الأعمدة
     */
    private function suggestMapping(array $headers): array
    {
        $mapping = [];
        $fieldNames = [
            'name' => ['name', 'اسم', 'الاسم', 'student_name', 'full_name'],
            'name_ar' => ['name_ar', 'اسم_عربي', 'الاسم_بالعربي', 'name_arabic', 'arabic_name'],
            'email' => ['email', 'بريد', 'البريد', 'mail', 'e-mail'],
            'password' => ['password', 'كلمة', 'pass', 'pwd'],
            'full_phone' => ['phone', 'هاتف', 'جوال', 'mobile', 'full_phone', 'الهاتف'],
            'course_name' => ['course', 'كورس', 'دورة', 'course_name'],
            'group_name' => ['group', 'مجموعة', 'group_name', 'فريق'],
        ];

        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            $matched = false;

            foreach ($fieldNames as $field => $keywords) {
                foreach ($keywords as $keyword) {
                    if (str_contains($headerLower, strtolower($keyword))) {
                        $mapping[$index] = $field;
                        $matched = true;
                        break 2;
                    }
                }
            }

            if (!$matched) {
                $mapping[$index] = 'skip';
            }
        }

        return $mapping;
    }
}
