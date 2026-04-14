<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['student.lesson.completed', 'database', 'ar', 'إكمال درس', 'أحسنت يا {{student_name}}', 'أكملت درس {{lesson_title}} بنجاح.'],
            ['student.course.completed', 'database', 'ar', 'إكمال كورس', 'مبروك {{student_name}}', 'أنهيت كورس {{course_title}} بالكامل.'],
            ['student.quiz.started', 'database', 'ar', 'بدء اختبار', 'تم بدء اختبار جديد', 'بدأت اختبار {{quiz_title}} (المحاولة {{attempt_number}}).'],
            ['student.quiz.completed', 'database', 'ar', 'إنهاء اختبار', 'نتيجة الاختبار', 'أنهيت اختبار {{quiz_title}} بنتيجة {{score}}.'],
            ['student.assignment.available', 'database', 'ar', 'واجب جديد', 'واجب متاح الآن', 'الواجب {{assignment_title}} أصبح متاحاً.'],
            ['student.assignment.submitted', 'database', 'ar', 'تسليم واجب', 'تم استلام تسليمك', 'تم تسليم واجب {{assignment_title}} بنجاح.'],
            ['student.assignment.graded', 'database', 'ar', 'تم التقييم', 'تقييم واجب', 'تم تقييم واجب {{assignment_title}} بدرجة {{grade}}.'],
            ['student.activity.tracked', 'database', 'ar', 'نشاط جديد', 'تم تسجيل نشاطك', 'نشاط: {{activity_key}}.'],
            ['admin.custom', 'database', 'ar', 'إشعار إداري', '{{title}}', '{{body}}'],
        ];

        foreach ($templates as [$eventKey, $channel, $locale, $name, $title, $body]) {
            NotificationTemplate::updateOrCreate(
                ['event_key' => $eventKey, 'channel' => $channel, 'locale' => $locale],
                [
                    'name' => $name,
                    'title_template' => $title,
                    'body_template' => $body,
                    'is_active' => true,
                ]
            );
        }
    }
}
