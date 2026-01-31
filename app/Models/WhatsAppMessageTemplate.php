<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppMessageTemplate extends Model
{
    protected $table = 'whatsapp_message_templates';

    protected $fillable = [
        'name',
        'slug',
        'body',
        'type',
        'language',
        'meta_template_name',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public const TYPE_TEXT = 'text';
    public const TYPE_TEMPLATE = 'template';

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get template body with variables replaced.
     *
     * @param array $replacements e.g. ['student_name' => 'أحمد', 'course_name' => 'البرمجة']
     * @return string
     */
    public function render(array $replacements = []): string
    {
        $body = $this->body;
        foreach ($replacements as $key => $value) {
            $body = str_replace(
                ['{{' . $key . '}}', '{' . $key . '}'],
                (string) $value,
                $body
            );
        }
        return $body;
    }

    /**
     * Find by slug (for programmatic use).
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::active()->where('slug', $slug)->first();
    }
}
