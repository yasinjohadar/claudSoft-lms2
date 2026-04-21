<?php

namespace App\WapiAutomation;

/**
 * مفاتيح أحداث متوافقة مع مركز الإشعارات حيث ينطبق، مع مفاتيح إضافية لمسارات تلقائية.
 */
final class WapiAutomationEventKey
{
    public const LESSON_COMPLETED = 'student.lesson.completed';

    public const COURSE_COMPLETED = 'student.course.completed';

    public const QUIZ_COMPLETED = 'student.quiz.completed';

    public const STUDENT_ENROLLED_IN_COURSE = 'student.course.enrolled';

    public const GROUP_REGISTRATION_SUBMITTED = 'group.registration.submitted';

    public const LESSON_BECAME_VISIBLE = 'lesson.became_visible';

    /** ارباط كورس بمجموعة أو تحديث رؤية — اختياري للتوسعة */
    public const COURSE_GROUP_UPDATED = 'course.group.updated';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::LESSON_COMPLETED => 'إكمال درس',
            self::COURSE_COMPLETED => 'إكمال كورس',
            self::QUIZ_COMPLETED => 'إكمال اختبار',
            self::STUDENT_ENROLLED_IN_COURSE => 'تسجيل الطالب في كورس',
            self::GROUP_REGISTRATION_SUBMITTED => 'تسجيل في مجموعة (طلب جديد)',
            self::LESSON_BECAME_VISIBLE => 'ظهور درس للطلاب',
            self::COURSE_GROUP_UPDATED => 'ربط كورس بمجموعة',
        ];
    }
}
