<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGiftRecipient extends Model
{
    protected $fillable = [
        'student_gift_id',
        'student_id',
        'granted_at',
        'previewed_at',
        'downloaded_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'previewed_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

    public function gift(): BelongsTo
    {
        return $this->belongsTo(StudentGift::class, 'student_gift_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
