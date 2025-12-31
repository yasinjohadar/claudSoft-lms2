<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AISetting extends Model
{
    use HasFactory;

    protected $table = 'ai_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
        'category',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the value attribute with proper casting
     */
    public function getValueAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        return match($this->type) {
            'integer' => (int) $value,
            'boolean' => (bool) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Set the value attribute with proper casting
     */
    public function setValueAttribute($value)
    {
        if ($value === null) {
            $this->attributes['value'] = null;
            return;
        }

        $this->attributes['value'] = match($this->type) {
            'integer' => (string) $value,
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }
}

