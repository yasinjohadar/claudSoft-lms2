<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\GamificationNotification;
use App\Notifications\CertificateIssuedNotification;
use App\Events\N8nWebhookEvent;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CertificateService
{
    /**
     * إصدار شهادة جديدة
     */
    public function issueCertificate(
        User $user,
        Course $course,
        CertificateTemplate $template,
        ?CourseEnrollment $enrollment = null,
        ?int $issuedBy = null
    ): Certificate {
        // التحقق من الأهلية
        if ($enrollment) {
            $eligibility = $template->checkEligibility($enrollment);
            if (!$eligibility['eligible']) {
                throw new \Exception('الطالب غير مؤهل للحصول على الشهادة: ' . implode(', ', $eligibility['reasons']));
            }
        }

        // إنشاء الشهادة
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'course_enrollment_id' => $enrollment?->id,
            'certificate_template_id' => $template->id,
            'issued_by' => $issuedBy,
            'student_name' => $user->name,
            'course_name' => $course->name,
            'course_name_en' => $course->name_en,
            'issue_date' => now(),
            'completion_date' => $enrollment?->completed_at ?? now(),
            'expiry_date' => $template->calculateExpiryDate(),
            'completion_percentage' => $enrollment?->completion_percentage,
            'attendance_percentage' => $enrollment?->attendance_percentage,
            'final_exam_score' => $enrollment?->grade,
            'course_hours' => $course->hours,
            'status' => 'active',
        ]);

        // إنشاء QR Code
        $this->generateQrCode($certificate);

        // إنشاء PDF
        $this->generatePdf($certificate);

        // تحديث حالة التسجيل
        if ($enrollment) {
            $enrollment->update([
                'certificate_eligible' => true,
                'certificate_issued_at' => now(),
            ]);
        }

        // إرسال إشعار Email عبر Laravel Notifications
        $user->notify(new CertificateIssuedNotification($certificate));

        // إرسال إشعار داخلي عبر Gamification Notifications
        GamificationNotification::create([
            'user_id' => $user->id,
            'type' => 'certificate_issued',
            'title' => '🎓 تم إصدار شهادتك!',
            'message' => 'تهانينا! تم إصدار شهادة إتمام كورس "' . $course->name . '" بنجاح.',
            'icon' => 'fas fa-certificate text-success fa-lg',
            'action_url' => route('student.certificates.show', $certificate->id),
            'related_type' => Certificate::class,
            'related_id' => $certificate->id,
            'metadata' => [
                'certificate_number' => $certificate->certificate_number,
                'course_name' => $course->name,
                'issue_date' => $certificate->issue_date->format('Y-m-d'),
            ],
            'is_read' => false,
        ]);

        // Dispatch n8n webhook event
        event(new N8nWebhookEvent('certificate.issued', [
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'verification_code' => $certificate->verification_code,
            'verification_url' => $certificate->verification_url,
            'student_id' => $user->id,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'course_id' => $course->id,
            'course_title' => $course->title ?? $course->name,
            'course_name' => $course->name,
            'template_id' => $template->id,
            'template_name' => $template->name ?? null,
            'issue_date' => $certificate->issue_date->toIso8601String(),
            'completion_date' => $certificate->completion_date?->toIso8601String(),
            'expiry_date' => $certificate->expiry_date?->toIso8601String(),
            'completion_percentage' => $certificate->completion_percentage,
            'attendance_percentage' => $certificate->attendance_percentage,
            'final_exam_score' => $certificate->final_exam_score,
            'course_hours' => $certificate->course_hours,
            'issued_by' => $issuedBy,
            'pdf_path' => $certificate->pdf_path,
            'qr_code_path' => $certificate->qr_code_path,
        ]));

        return $certificate->fresh();
    }

    /**
     * توليد QR Code للشهادة
     */
    public function generateQrCode(Certificate $certificate): string
    {
        $qrCodePath = 'certificates/qr-codes/' . $certificate->verification_code . '.png';
        $qrCodeFullPath = storage_path('app/public/' . $qrCodePath);

        // إنشاء المجلد إذا لم يكن موجوداً
        if (!file_exists(dirname($qrCodeFullPath))) {
            mkdir(dirname($qrCodeFullPath), 0755, true);
        }

        // توليد QR Code
        QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($certificate->verification_url, $qrCodeFullPath);

        // تحديث الشهادة
        $certificate->update(['qr_code_path' => $qrCodePath]);

        return $qrCodePath;
    }

    /**
     * توليد PDF للشهادة
     */
    public function generatePdf(Certificate $certificate): string
    {
        $template = $certificate->template;

        if ($template->isImageType()) {
            return $this->generateImagePdf($certificate);
        }

        return $this->generateHtmlPdf($certificate);
    }

    /**
     * توليد PDF من HTML Template
     */
    protected function generateHtmlPdf(Certificate $certificate): string
    {
        $template = $certificate->template;

        // استبدال الحقول الديناميكية
        $html = $this->replaceDynamicFields($template->html_content ?? '', $certificate);

        // إنشاء PDF
        $pdf = Pdf::loadHTML($html)
            ->setPaper($template->page_size, $template->orientation)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        // حفظ PDF
        $pdfPath = 'certificates/pdf/' . $certificate->certificate_number . '.pdf';
        $pdfFullPath = storage_path('app/public/' . $pdfPath);

        if (!file_exists(dirname($pdfFullPath))) {
            mkdir(dirname($pdfFullPath), 0755, true);
        }

        $pdf->save($pdfFullPath);

        // تحديث الشهادة
        $certificate->update(['pdf_path' => $pdfPath]);

        return $pdfPath;
    }

    /**
     * توليد PDF من Image Template (صورة مع نصوص)
     */
    protected function generateImagePdf(Certificate $certificate): string
    {
        $template = $certificate->template;

        if (!$template->hasTemplateFile()) {
            throw new \Exception('ملف القالب غير موجود');
        }

        // قراءة صورة القالب
        $templatePath = storage_path('app/public/' . $template->template_file);

        // إنشاء مدير الصور
        $manager = new ImageManager(new Driver());
        $image = $manager->read($templatePath);

        // إضافة النصوص على الصورة
        $fieldPositions = $template->field_positions ?? [];

        foreach ($fieldPositions as $field => $position) {
            $text = $this->getFieldValue($field, $certificate);

            if (!empty($text) && isset($position['x'], $position['y'])) {
                $image->text(
                    $text,
                    (int)$position['x'],
                    (int)$position['y'],
                    function ($font) use ($position) {
                        $font->filename(public_path('fonts/NotoKufiArabic-Regular.ttf'));
                        $font->size($position['size'] ?? 24);
                        $font->color($position['color'] ?? '#000000');
                        $font->align($position['align'] ?? 'center');
                        $font->valign($position['valign'] ?? 'middle');
                    }
                );
            }
        }

        // إضافة QR Code إذا كان موجوداً
        if ($certificate->qr_code_path && isset($fieldPositions['{qr_code}'])) {
            $qrPosition = $fieldPositions['{qr_code}'];
            $qrCodePath = storage_path('app/public/' . $certificate->qr_code_path);

            if (file_exists($qrCodePath)) {
                $qrImage = $manager->read($qrCodePath);
                $qrImage->resize($qrPosition['width'] ?? 150, $qrPosition['height'] ?? 150);

                $image->place(
                    $qrImage,
                    'top-left',
                    (int)($qrPosition['x'] ?? 0),
                    (int)($qrPosition['y'] ?? 0)
                );
            }
        }

        // حفظ الصورة المعدلة
        $imagePath = 'certificates/images/' . $certificate->certificate_number . '.png';
        $imageFullPath = storage_path('app/public/' . $imagePath);

        if (!file_exists(dirname($imageFullPath))) {
            mkdir(dirname($imageFullPath), 0755, true);
        }

        $image->save($imageFullPath);

        // تحويل الصورة إلى PDF
        $html = '<html><body style="margin:0;padding:0;"><img src="' . $imageFullPath . '" style="width:100%;height:100%;"/></body></html>';

        $pdf = Pdf::loadHTML($html)
            ->setPaper($template->page_size, $template->orientation);

        $pdfPath = 'certificates/pdf/' . $certificate->certificate_number . '.pdf';
        $pdfFullPath = storage_path('app/public/' . $pdfPath);

        if (!file_exists(dirname($pdfFullPath))) {
            mkdir(dirname($pdfFullPath), 0755, true);
        }

        $pdf->save($pdfFullPath);

        // تحديث الشهادة
        $certificate->update(['pdf_path' => $pdfPath]);

        return $pdfPath;
    }

    /**
     * استبدال الحقول الديناميكية في HTML
     */
    protected function replaceDynamicFields(string $html, Certificate $certificate): string
    {
        $replacements = [
            '{student_name}' => $certificate->student_name,
            '{student_name_en}' => $certificate->student->name_en ?? $certificate->student_name,
            '{course_name}' => $certificate->course_name,
            '{course_name_en}' => $certificate->course_name_en ?? $certificate->course_name,
            '{certificate_number}' => $certificate->certificate_number,
            '{issue_date}' => $certificate->issue_date->format('Y-m-d'),
            '{issue_date_ar}' => $certificate->issue_date->locale('ar')->translatedFormat('d F Y'),
            '{completion_date}' => $certificate->completion_date?->format('Y-m-d') ?? '-',
            '{expiry_date}' => $certificate->expiry_date?->format('Y-m-d') ?? '-',
            '{completion_percentage}' => $certificate->completion_percentage ?? '-',
            '{attendance_percentage}' => $certificate->attendance_percentage ?? '-',
            '{final_exam_score}' => $certificate->final_exam_score ?? '-',
            '{course_hours}' => $certificate->course_hours ?? '-',
            '{verification_code}' => $certificate->verification_code,
            '{verification_url}' => $certificate->verification_url,
        ];

        // QR Code
        if ($certificate->qr_code_path) {
            $qrCodeUrl = storage_url($certificate->qr_code_path);
            $replacements['{qr_code}'] = '<img src="' . $qrCodeUrl . '" width="150" height="150" />';
        } else {
            $replacements['{qr_code}'] = '';
        }

        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }

    /**
     * الحصول على قيمة حقل معين
     */
    protected function getFieldValue(string $field, Certificate $certificate): string
    {
        return match ($field) {
            '{student_name}' => $certificate->student_name,
            '{student_name_en}' => $certificate->student->name_en ?? $certificate->student_name,
            '{course_name}' => $certificate->course_name,
            '{course_name_en}' => $certificate->course_name_en ?? $certificate->course_name,
            '{certificate_number}' => $certificate->certificate_number,
            '{issue_date}' => $certificate->issue_date->format('Y-m-d'),
            '{issue_date_ar}' => $certificate->issue_date->locale('ar')->translatedFormat('d F Y'),
            '{completion_date}' => $certificate->completion_date?->format('Y-m-d') ?? '-',
            '{expiry_date}' => $certificate->expiry_date?->format('Y-m-d') ?? '-',
            '{completion_percentage}' => (string)($certificate->completion_percentage ?? '-'),
            '{attendance_percentage}' => (string)($certificate->attendance_percentage ?? '-'),
            '{final_exam_score}' => (string)($certificate->final_exam_score ?? '-'),
            '{course_hours}' => (string)($certificate->course_hours ?? '-'),
            '{verification_code}' => $certificate->verification_code,
            default => '',
        };
    }

    /**
     * التحقق من صحة الشهادة
     */
    public function verifyCertificate(string $verificationCode): ?Certificate
    {
        $certificate = Certificate::byVerificationCode($verificationCode)->first();

        if (!$certificate) {
            return null;
        }

        // التحقق من انتهاء الصلاحية
        $certificate->checkExpiry();

        return $certificate;
    }

    /**
     * إعادة إصدار الشهادة
     */
    public function reissueCertificate(Certificate $certificate, int $issuedBy): Certificate
    {
        // حذف الملفات القديمة
        $this->deleteCertificateFiles($certificate);

        // إعادة توليد QR Code و PDF
        $this->generateQrCode($certificate);
        $this->generatePdf($certificate);

        // تحديث معلومات الإصدار
        $certificate->update([
            'issued_by' => $issuedBy,
            'issue_date' => now(),
        ]);

        return $certificate->fresh();
    }

    /**
     * إلغاء الشهادة
     */
    public function revokeCertificate(Certificate $certificate, string $reason, int $revokedBy): bool
    {
        return $certificate->revoke($reason, $revokedBy);
    }

    /**
     * حذف ملفات الشهادة
     */
    protected function deleteCertificateFiles(Certificate $certificate): void
    {
        if ($certificate->pdf_path && cloud_file_exists($certificate->pdf_path)) {
            cloud_delete_file($certificate->pdf_path);
        }

        if ($certificate->qr_code_path && cloud_file_exists($certificate->qr_code_path)) {
            cloud_delete_file($certificate->qr_code_path);
        }
    }

    /**
     * إصدار شهادات تلقائية لجميع الطلاب المؤهلين في كورس
     */
    public function issueAutomaticCertificates(Course $course, CertificateTemplate $template): array
    {
        $issued = [];
        $failed = [];

        $enrollments = CourseEnrollment::where('course_id', $course->id)
            ->where('enrollment_status', 'completed')
            ->whereNull('certificate_issued_at')
            ->with('student')
            ->get();

        foreach ($enrollments as $enrollment) {
            try {
                $eligibility = $template->checkEligibility($enrollment);

                if ($eligibility['eligible']) {
                    $certificate = $this->issueCertificate(
                        $enrollment->student,
                        $course,
                        $template,
                        $enrollment
                    );

                    $issued[] = [
                        'student' => $enrollment->student->name,
                        'certificate_number' => $certificate->certificate_number,
                    ];
                } else {
                    $failed[] = [
                        'student' => $enrollment->student->name,
                        'reason' => implode(', ', $eligibility['reasons']),
                    ];
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'student' => $enrollment->student->name,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return [
            'issued' => $issued,
            'failed' => $failed,
            'total_issued' => count($issued),
            'total_failed' => count($failed),
        ];
    }

    /**
     * الحصول على إحصائيات الشهادات
     */
    public function getStatistics(): array
    {
        return [
            'total' => Certificate::count(),
            'active' => Certificate::active()->count(),
            'revoked' => Certificate::revoked()->count(),
            'expired' => Certificate::expired()->count(),
            'issued_this_month' => Certificate::whereMonth('issue_date', now()->month)
                ->whereYear('issue_date', now()->year)
                ->count(),
            'issued_this_year' => Certificate::whereYear('issue_date', now()->year)->count(),
        ];
    }
}
