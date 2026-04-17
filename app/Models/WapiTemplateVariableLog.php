<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WapiTemplateVariableLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'wapi_template_id',
        'variables',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WapiTemplate::class, 'wapi_template_id');
    }
}
