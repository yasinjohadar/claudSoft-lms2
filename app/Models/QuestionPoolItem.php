<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionPoolItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'question_pool_items';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pool_id',
        'question_id',
    ];

    /**
     * Get the pool that owns this item.
     */
    public function pool()
    {
        return $this->belongsTo(QuestionPool::class, 'pool_id');
    }

    /**
     * Get the question for this pool item.
     */
    public function question()
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }
}
