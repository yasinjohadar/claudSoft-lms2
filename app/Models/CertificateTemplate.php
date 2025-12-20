<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'type',
        'template_file',
        'html_content',
        'field_positions',
        'dynamic_fields',
        'requirements',
        'auto_issue',
        'requires_attendance',
        'min_attendance_percentage',
        'requires_completion',
        'min_completion_percentage',
        'requires_final_exam',
        'min_final_exam_score',
        'has_expiry',
        'expiry_months',
        'orientation',
        'page_size',
        'is_active',
        'is_default',
        'display_order',
    ];

    protected $casts = [
        'field_positions' => 'array',
        'dynamic_fields' => 'array',
        'requirements' => 'array',
        'auto_issue' => 'boolean',
        'requires_attendance' => 'boolean',
        'requires_completion' => 'boolean',
        'requires_final_exam' => 'boolean',
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    // Relationships
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'certificate_template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeAutoIssue($query)
    {
        return $query->where('auto_issue', true);
    }

    public function scopeHtmlType($query)
    {
        return $query->where('type', 'html');
    }

    public function scopeImageType($query)
    {
        return $query->where('type', 'image');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    // Accessors
    public function getTemplateFileUrlAttribute(): ?string
    {
        return $this->template_file ? asset("storage/{$this->template_file}") : null;
    }

    public function getAvailableFieldsAttribute(): array
    {
        return [
            '{student_name}' => 'اسم الطالب',
            '{student_name_en}' => 'Student Name',
            '{course_name}' => 'اسم الكورس',
            '{course_name_en}' => 'Course Name',
            '{certificate_number}' => 'رقم الشهادة',
            '{issue_date}' => 'تاريخ الإصدار',
            '{completion_date}' => 'تاريخ الإكمال',
            '{expiry_date}' => 'تاريخ الانتهاء',
            '{completion_percentage}' => 'نسبة الإكمال',
            '{attendance_percentage}' => 'نسبة الحضور',
            '{final_exam_score}' => 'درجة الاختبار النهائي',
            '{course_hours}' => 'عدد الساعات',
            '{verification_code}' => 'كود التحقق',
            '{qr_code}' => 'QR Code',
        ];
    }

    // Methods
    public function checkEligibility($enrollment): array
    {
        $eligible = true;
        $reasons = [];

        // Check completion percentage
        if ($this->requires_completion) {
            $completionPercentage = $enrollment->completion_percentage ?? 0;
            if ($completionPercentage < $this->min_completion_percentage) {
                $eligible = false;
                $reasons[] = "نسبة الإكمال ({$completionPercentage}%) أقل من المطلوب ({$this->min_completion_percentage}%)";
            }
        }

        // Check attendance percentage
        if ($this->requires_attendance && $this->min_attendance_percentage) {
            $attendancePercentage = $enrollment->attendance_percentage ?? 0;
            if ($attendancePercentage < $this->min_attendance_percentage) {
                $eligible = false;
                $reasons[] = "نسبة الحضور ({$attendancePercentage}%) أقل من المطلوب ({$this->min_attendance_percentage}%)";
            }
        }

        // Check final exam score
        if ($this->requires_final_exam && $this->min_final_exam_score) {
            $finalExamScore = $enrollment->grade ?? 0;
            if ($finalExamScore < $this->min_final_exam_score) {
                $eligible = false;
                $reasons[] = "درجة الاختبار النهائي ({$finalExamScore}) أقل من المطلوب ({$this->min_final_exam_score})";
            }
        }

        return [
            'eligible' => $eligible,
            'reasons' => $reasons,
        ];
    }

    public function calculateExpiryDate($issueDate = null): ?string
    {
        if (!$this->has_expiry || !$this->expiry_months) {
            return null;
        }

        $date = $issueDate ? \Carbon\Carbon::parse($issueDate) : now();
        return $date->addMonths($this->expiry_months)->format('Y-m-d');
    }

    public function setAsDefault(): bool
    {
        // Remove default from all other templates
        static::where('id', '!=', $this->id)->update(['is_default' => false]);

        // Set this as default
        return $this->update(['is_default' => true]);
    }

    public function isHtmlType(): bool
    {
        return $this->type === 'html';
    }

    public function isImageType(): bool
    {
        return $this->type === 'image';
    }

    public function hasTemplateFile(): bool
    {
        return $this->template_file && file_exists(storage_path("app/public/{$this->template_file}"));
    }

    public function getRequirementsSummary(): string
    {
        $summary = [];

        if ($this->requires_completion) {
            $summary[] = "إكمال {$this->min_completion_percentage}%";
        }

        if ($this->requires_attendance && $this->min_attendance_percentage) {
            $summary[] = "حضور {$this->min_attendance_percentage}%";
        }

        if ($this->requires_final_exam && $this->min_final_exam_score) {
            $summary[] = "اختبار نهائي {$this->min_final_exam_score}%";
        }

        return implode(' + ', $summary) ?: 'لا توجد متطلبات';
    }

    // Static Methods
    public static function getDefaultTemplate()
    {
        return static::where('is_default', true)->first() ?? static::active()->first();
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            // Set as default if no default exists
            if (!static::where('is_default', true)->exists()) {
                $template->is_default = true;
            }

            // Set display order if not set
            if (!$template->display_order) {
                $maxOrder = static::max('display_order') ?? 0;
                $template->display_order = $maxOrder + 1;
            }
        });

        static::deleting(function ($template) {
            // Prevent deletion if it's the default template and only template
            if ($template->is_default && static::count() === 1) {
                throw new \Exception('لا يمكن حذف القالب الافتراضي الوحيد');
            }

            // If deleting default template, set another as default
            if ($template->is_default) {
                $newDefault = static::where('id', '!=', $template->id)
                    ->active()
                    ->orderBy('display_order')
                    ->first();

                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }
        });
    }
}
