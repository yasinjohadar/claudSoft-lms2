<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'sent_by',
        'title',
        'message',
        'type',
        'is_message',
        'action_url',
        'recipients_count',
    ];

    protected $casts = [
        'recipients_count' => 'integer',
        'is_message' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Per-recipient copies live in GamificationNotification.metadata->group_notification_id
     * (a JSON key, not a real column), so this is a query builder, not an Eloquent relation.
     */
    public function recipientsQuery()
    {
        return GamificationNotification::where('metadata->group_notification_id', (string) $this->id);
    }

    public function readCount(): int
    {
        return $this->recipientsQuery()->where('is_read', true)->count();
    }
}
