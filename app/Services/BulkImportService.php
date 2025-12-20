<?php

namespace App\Services;

use App\Models\BulkImportSession;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseGroup;
use App\Models\CourseGroupMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BulkImportService
{
    /**
     * قراءة ملف Excel وإرجاع البيانات
     */
    public function parseExcelFile(string $filePath): array
    {
        try {
            // التحقق من وجود الملف
            if (!file_exists($filePath)) {
                throw new \Exception('الملف غير موجود: ' . $filePath);
            }

            // التحقق من إمكانية القراءة
            if (!is_readable($filePath)) {
                throw new \Exception('لا يمكن قراءة الملف. تأكد من صلاحيات الملف.');
            }

            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                throw new \Exception('الملف فارغ');
            }

            // الصف الأول هو الـ headers
            $headers = array_shift($rows);

            // إزالة الصفوف الفارغة
            $rows = array_filter($rows, function ($row) {
                return !empty(array_filter($row));
            });

            return [
                'headers' => $headers,
                'rows' => array_values($rows),
                'total' => count($rows),
            ];
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            throw new \Exception('خطأ في قراءة ملف Excel: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('خطأ في قراءة الملف: ' . $e->getMessage());
        }
    }

    /**
     * التحقق من صحة صف واحد
     */
    public function validateRow(array $row, array $mapping): array
    {
        $data = $this->mapRowToData($row, $mapping);

        $rules = [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'full_phone' => 'nullable|string|max:20',
            'course_name' => 'nullable|string|max:255',
            'group_name' => 'nullable|string|max:255',
        ];

        $validator = Validator::make($data, $rules, [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all(),
            ];
        }

        return [
            'valid' => true,
            'data' => $data,
        ];
    }

    /**
     * تحويل الصف إلى بيانات حسب الـ mapping
     */
    private function mapRowToData(array $row, array $mapping): array
    {
        $data = [];

        foreach ($mapping as $index => $field) {
            if ($field !== 'skip' && isset($row[$index])) {
                $data[$field] = $row[$index];
            }
        }

        return $data;
    }

    /**
     * إنشاء أو تحديث مستخدم
     */
    public function createOrUpdateUser(array $data, bool $updateExisting = true): array
    {
        $user = User::where('email', $data['email'])->first();

        $updatedFields = [];
        $isNew = false;

        DB::beginTransaction();
        try {
            if ($user) {
                // المستخدم موجود
                if (!$updateExisting) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'skipped' => true,
                        'message' => 'المستخدم موجود مسبقاً - تم التخطي',
                    ];
                }

                // تحديث البيانات
                $oldData = [
                    'name' => $user->name,
                    'name_ar' => $user->name_ar,
                    'full_phone' => $user->full_phone,
                ];

                if (isset($data['name']) && $user->name !== $data['name']) {
                    $user->name = $data['name'];
                    $updatedFields[] = 'الاسم';
                }

                if (isset($data['name_ar']) && $user->name_ar !== $data['name_ar']) {
                    $user->name_ar = $data['name_ar'];
                    $updatedFields[] = 'الاسم بالعربي';
                }

                if (isset($data['password'])) {
                    $user->password = Hash::make($data['password']);
                    $updatedFields[] = 'كلمة المرور';
                }

                if (isset($data['full_phone']) && !empty($data['full_phone'])) {
                    $phoneComponents = $this->extractPhoneComponents($data['full_phone']);
                    $user->country_code = $phoneComponents['country_code'];
                    $user->phone = $phoneComponents['phone'];
                    $user->full_phone = $phoneComponents['full_phone'];
                    $updatedFields[] = 'رقم الهاتف';
                }

                $user->save();

                DB::commit();
                return [
                    'success' => true,
                    'updated' => true,
                    'user' => $user,
                    'updated_fields' => $updatedFields,
                ];
            } else {
                // مستخدم جديد
                $isNew = true;

                $userData = [
                    'name' => $data['name'],
                    'name_ar' => $data['name_ar'] ?? null,
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'is_active' => true,
                ];

                // معالجة رقم الهاتف
                if (isset($data['full_phone']) && !empty($data['full_phone'])) {
                    $phoneComponents = $this->extractPhoneComponents($data['full_phone']);
                    $userData['country_code'] = $phoneComponents['country_code'];
                    $userData['phone'] = $phoneComponents['phone'];
                    $userData['full_phone'] = $phoneComponents['full_phone'];
                }

                $user = User::create($userData);

                // تعيين صلاحية student
                $user->assignRole('student');

                DB::commit();
                return [
                    'success' => true,
                    'new' => true,
                    'user' => $user,
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * استخراج country_code و phone من full_phone
     */
    public function extractPhoneComponents(string $fullPhone): array
    {
        // إزالة أي رموز غير رقمية
        $cleaned = preg_replace('/\D/', '', $fullPhone);

        // قائمة رموز الدول
        $countryCodes = [
            '966' => '+966', // السعودية
            '971' => '+971', // الإمارات
            '20' => '+20',   // مصر
            '962' => '+962', // الأردن
            '965' => '+965', // الكويت
            '973' => '+973', // البحرين
            '974' => '+974', // قطر
            '968' => '+968', // عمان
            '961' => '+961', // لبنان
            '963' => '+963', // سوريا
            '964' => '+964', // العراق
            '967' => '+967', // اليمن
            '212' => '+212', // المغرب
            '213' => '+213', // الجزائر
            '216' => '+216', // تونس
            '218' => '+218', // ليبيا
            '249' => '+249', // السودان
            '90' => '+90',   // تركيا
            '1' => '+1',     // أمريكا
            '44' => '+44',   // بريطانيا
        ];

        // محاولة إيجاد رمز الدولة
        foreach ($countryCodes as $code => $formatted) {
            if (str_starts_with($cleaned, $code)) {
                return [
                    'country_code' => $formatted,
                    'phone' => substr($cleaned, strlen($code)),
                    'full_phone' => '+' . $cleaned,
                ];
            }
        }

        // افتراضي: السعودية إذا لم يُعثر على رمز
        $cleanedPhone = ltrim($cleaned, '0');
        return [
            'country_code' => '+966',
            'phone' => $cleanedPhone,
            'full_phone' => '+966' . $cleanedPhone,
        ];
    }

    /**
     * تسجيل المستخدم في كورس
     */
    public function enrollUserInCourse(User $user, string $courseName, int $enrolledBy): array
    {
        $course = Course::where('title', $courseName)->first();

        if (!$course) {
            return [
                'success' => false,
                'error' => 'الكورس "' . $courseName . '" غير موجود',
            ];
        }

        // التحقق من التسجيل المسبق
        $existingEnrollment = CourseEnrollment::where('course_id', $course->id)
            ->where('student_id', $user->id)
            ->first();

        if ($existingEnrollment) {
            return [
                'success' => false,
                'skipped' => true,
                'message' => 'الطالب مسجل بالفعل في هذا الكورس',
            ];
        }

        try {
            DB::beginTransaction();

            CourseEnrollment::create([
                'course_id' => $course->id,
                'student_id' => $user->id,
                'enrolled_by' => $enrolledBy,
                'enrollment_date' => now(),
                'enrollment_status' => 'active',
                'completion_percentage' => 0,
                'certificate_issued' => false,
            ]);

            DB::commit();

            return [
                'success' => true,
                'course' => $course->title,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * إضافة المستخدم إلى مجموعة
     */
    public function addUserToGroup(User $user, string $groupName): array
    {
        $group = CourseGroup::where('name', $groupName)->first();

        if (!$group) {
            return [
                'success' => false,
                'error' => 'المجموعة "' . $groupName . '" غير موجودة',
            ];
        }

        // التحقق من العضوية المسبقة
        $existingMember = CourseGroupMember::where('group_id', $group->id)
            ->where('student_id', $user->id)
            ->first();

        if ($existingMember) {
            return [
                'success' => false,
                'skipped' => true,
                'message' => 'الطالب عضو بالفعل في هذه المجموعة',
            ];
        }

        // التحقق من امتلاء المجموعة
        if ($group->max_members) {
            $currentMembers = CourseGroupMember::where('group_id', $group->id)->count();
            if ($currentMembers >= $group->max_members) {
                return [
                    'success' => false,
                    'error' => 'المجموعة ممتلئة (الحد الأقصى: ' . $group->max_members . ')',
                ];
            }
        }

        try {
            DB::beginTransaction();

            CourseGroupMember::create([
                'group_id' => $group->id,
                'student_id' => $user->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'group' => $group->name,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * معالجة صف واحد
     */
    public function processRow(
        array $row,
        array $mapping,
        BulkImportSession $session,
        int $rowNumber,
        bool $updateExisting = true,
        bool $skipErrors = true
    ): array {
        // التحقق من الصحة
        $validation = $this->validateRow($row, $mapping);

        if (!$validation['valid']) {
            $errorMessage = implode(', ', $validation['errors']);
            $session->addError($rowNumber, $row[array_search('email', $mapping)] ?? 'غير معروف', $errorMessage);
            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        }

        $data = $validation['data'];

        // إنشاء أو تحديث المستخدم
        $userResult = $this->createOrUpdateUser($data, $updateExisting);

        if (!$userResult['success']) {
            if (isset($userResult['skipped']) && $userResult['skipped']) {
                $session->incrementSkipped();
                return ['success' => true, 'skipped' => true];
            }

            $session->addError($rowNumber, $data['email'], $userResult['error']);
            return [
                'success' => false,
                'error' => $userResult['error'],
            ];
        }

        $user = $userResult['user'];

        // تسجيل التفاصيل
        if (isset($userResult['new']) && $userResult['new']) {
            $session->addNewUser([
                'name' => $user->name,
                'email' => $user->email,
                'course' => $data['course_name'] ?? null,
                'group' => $data['group_name'] ?? null,
            ]);
        } elseif (isset($userResult['updated']) && $userResult['updated']) {
            $session->addUpdatedUser([
                'name' => $user->name,
                'email' => $user->email,
                'updated_fields' => $userResult['updated_fields'],
            ]);
        }

        // تسجيل في الكورس
        if (!empty($data['course_name'])) {
            $enrollResult = $this->enrollUserInCourse($user, $data['course_name'], auth()->id());
            if ($enrollResult['success']) {
                $session->incrementEnrollments();
            } elseif (!isset($enrollResult['skipped'])) {
                // خطأ في التسجيل
                if (!$skipErrors) {
                    $session->addError($rowNumber, $data['email'], $enrollResult['error']);
                }
            }
        }

        // إضافة للمجموعة
        if (!empty($data['group_name'])) {
            $groupResult = $this->addUserToGroup($user, $data['group_name']);
            if ($groupResult['success']) {
                $session->incrementGroupMembers();
            } elseif (!isset($groupResult['skipped'])) {
                // خطأ في الإضافة
                if (!$skipErrors) {
                    $session->addError($rowNumber, $data['email'], $groupResult['error']);
                }
            }
        }

        return ['success' => true];
    }

    /**
     * إنشاء ملف Excel للأخطاء
     */
    public function generateErrorsFile(BulkImportSession $session): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'رقم الصف');
        $sheet->setCellValue('B1', 'البريد الإلكتروني');
        $sheet->setCellValue('C1', 'الخطأ');

        // تنسيق الـ header
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        // البيانات
        $row = 2;
        foreach ($session->errors as $error) {
            $sheet->setCellValue('A' . $row, $error['row']);
            $sheet->setCellValue('B' . $row, $error['email']);
            $sheet->setCellValue('C' . $row, $error['message']);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // حفظ الملف
        $fileName = 'errors_' . $session->id . '_' . time() . '.xlsx';
        $filePath = storage_path('app/imports/errors/' . $fileName);

        // إنشاء المجلد إذا لم يكن موجوداً
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }
}
