<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramMessageTemplate extends Model
{
    protected $fillable = ['name', 'slug', 'body', 'variables', 'is_active'];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function render(array $replacements = []): string
    {
        $body = $this->body;
        foreach ($replacements as $key => $value) {
            $body = str_replace(
                ['{{'.$key.'}}', '{'.$key.'}'],
                (string) $value,
                $body
            );
        }

        return WhatsAppMessageTemplate::normalizeBodyForSending($body);
    }
}
