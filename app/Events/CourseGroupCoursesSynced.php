<?php

namespace App\Events;

use App\Models\CourseGroup;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُطلق بعد ربط كورسات جديدة بمجموعة (إنشاء مجموعة أو تحديث ربط الكورسات).
 */
class CourseGroupCoursesSynced
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<int, int>  $addedCourseIds  معرفات الكورسات المضافة حديثاً للمجموعة
     */
    public function __construct(
        public CourseGroup $group,
        public array $addedCourseIds
    ) {}
}
