<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'subject',
        'body',
        'type',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Template types
     */
    public const TYPE_REGISTRATION_WELCOME = 'registration_welcome';
    public const TYPE_ENROLLMENT_CONFIRMATION = 'enrollment_confirmation';
    public const TYPE_CUSTOM = 'custom';

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helper Methods

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Render template with variables
     */
    public function render(array $variables = []): string
    {
        $body = $this->body;
        $subject = $this->subject;

        foreach ($variables as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }

        return $body;
    }

    public function renderSubject(array $variables = []): string
    {
        $subject = $this->subject;

        foreach ($variables as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }

        return $subject;
    }

    /**
     * استخراج المتغيرات المستخدمة في القالب
     */
    public function getUsedVariables(): array
    {
        $variables = [];
        $pattern = '/\{\{(\w+)\}\}/';
        
        // البحث في subject
        preg_match_all($pattern, $this->subject, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }
        
        // البحث في body
        preg_match_all($pattern, $this->body, $matches);
        if (!empty($matches[1])) {
            $variables = array_merge($variables, $matches[1]);
        }
        
        return array_unique($variables);
    }

    /**
     * التحقق من صحة القالب (جميع المتغيرات موجودة)
     */
    public function validateVariables(array $providedVariables): array
    {
        $usedVariables = $this->getUsedVariables();
        $missing = [];
        
        foreach ($usedVariables as $var) {
            if (!isset($providedVariables[$var])) {
                $missing[] = $var;
            }
        }
        
        return $missing;
    }

    /**
     * الحصول على قائمة المتغيرات المتاحة حسب نوع القالب
     */
    public static function getAvailableVariablesByType(string $type): array
    {
        $variables = [
            'student_name' => 'اسم الطالب (عربي)',
            'student_name_en' => 'اسم الطالب (إنجليزي)',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
        ];

        if ($type === self::TYPE_REGISTRATION_WELCOME || $type === self::TYPE_ENROLLMENT_CONFIRMATION) {
            $variables['group_name'] = 'اسم المجموعة';
        }

        if ($type === self::TYPE_CUSTOM) {
            $variables['group_name'] = 'اسم المجموعة';
            $variables['course_name'] = 'اسم الكورس';
        }

        return $variables;
    }
}
