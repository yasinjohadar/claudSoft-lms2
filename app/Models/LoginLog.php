<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $fillable = [
        'user_id','ip_address','user_agent','device_type','browser','browser_version',
        'platform','platform_version','country','city','is_successful','failure_reason',
        'login_at','logout_at','session_duration_seconds','session_id','meta',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
        'login_at'      => 'datetime',
        'logout_at'     => 'datetime',
        'meta'          => 'array',
    ];
}
