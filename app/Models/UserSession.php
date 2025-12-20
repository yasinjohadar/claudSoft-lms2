<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = [
        'user_id','session_uuid','session_name','session_description',
        'started_at','ended_at','duration_seconds','ip_address','user_agent',
        'device_type','browser','browser_version','platform','platform_version',
        'screen_resolution','connection_type','bandwidth_mbps','status','meta',
        'login_log_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'duration_seconds' => 'integer',
        'meta'       => 'array',
    ];

    /**
     * العلاقة مع User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع SessionActivities
     */
    public function activities()
    {
        return $this->hasMany(SessionActivity::class, 'user_session_id')->latest('occurred_at');
    }
}
