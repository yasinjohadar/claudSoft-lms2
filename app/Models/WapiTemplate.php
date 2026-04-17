<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WapiTemplate extends Model
{
    protected $fillable = [
        'name',
        'language',
        'structure',
        'provider_template_id',
    ];

    protected function casts(): array
    {
        return [
            'structure' => 'array',
        ];
    }

    public function variableLogs(): HasMany
    {
        return $this->hasMany(WapiTemplateVariableLog::class, 'wapi_template_id');
    }
}
