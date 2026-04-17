<?php

namespace App\Models;

use App\Enums\WapiMessageStatus;
use App\Enums\WapiMessageType;
use Illuminate\Database\Eloquent\Model;

class WapiMessage extends Model
{
    protected $fillable = [
        'phone',
        'type',
        'content',
        'status',
        'response',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'response' => 'array',
            'type' => WapiMessageType::class,
            'status' => WapiMessageStatus::class,
        ];
    }
}
