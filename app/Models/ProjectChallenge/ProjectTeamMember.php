<?php

namespace App\Models\ProjectChallenge;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_team_id',
        'user_id',
        'role',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(ProjectTeam::class, 'project_team_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLeaders($query)
    {
        return $query->where('role', 'leader');
    }

    public function isLeader(): bool
    {
        return $this->role === 'leader';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
