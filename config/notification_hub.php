<?php

return [
    'channels' => [
        'database' => env('NOTIFY_CHANNEL_DATABASE', true),
        'realtime' => env('NOTIFY_CHANNEL_REALTIME', true),
        'fcm' => env('NOTIFY_CHANNEL_FCM', false),
        'mail' => env('NOTIFY_CHANNEL_MAIL', true),
        'whatsapp' => env('NOTIFY_CHANNEL_WHATSAPP', true),
        /** إرسال قوالب Flaxxa WAPI تلقائياً حسب قواعد wapi_automation_rules */
        'whatsapp_wapi' => env('NOTIFY_CHANNEL_WHATSAPP_WAPI', true),
    ],

    'fcm' => [
        'enabled' => env('FCM_ENABLED', false),
        'server_key' => env('FCM_SERVER_KEY'),
        'endpoint' => env('FCM_ENDPOINT', 'https://fcm.googleapis.com/fcm/send'),
        'timeout' => env('FCM_TIMEOUT', 20),
    ],

    'defaults' => [
        'locale' => env('NOTIFY_DEFAULT_LOCALE', 'ar'),
    ],

    /*
    | نصوص احتياطية عندما لا يُوجد صف في notification_templates (title_template / body_template).
    | الأحداث الفرعية لـ student.activity.tracked تُعرّف تحت activity_fallbacks بالمفتاح activity_key.
    */
    'event_fallbacks' => [
        'student.lesson.completed' => [
            'title' => 'إكمال درس',
            'body' => 'أكملت درس «{{lesson_title}}» ضمن المساق.',
        ],
        'student.course.completed' => [
            'title' => 'إكمال كورس',
            'body' => 'أنهيتَ كورس «{{course_title}}». مبروك!',
        ],
        'student.quiz.started' => [
            'title' => 'بدء اختبار',
            'body' => 'بدأتَ اختبار «{{quiz_title}}».',
        ],
        'student.quiz.completed' => [
            'title' => 'نتيجة اختبار',
            'body' => 'أنهيتَ اختبار «{{quiz_title}}» بنتيجة {{score}} من {{total_questions}}.',
        ],
        'student.assignment.submitted' => [
            'title' => 'تسليم واجب',
            'body' => 'تم تسليم واجب «{{assignment_title}}».',
        ],
        'student.assignment.available' => [
            'title' => 'واجب متاح',
            'body' => 'الواجب «{{assignment_title}}» أصبح متاحاً.',
        ],
        'student.assignment.graded' => [
            'title' => 'تم تقييم الواجب',
            'body' => 'تم تقييم واجب «{{assignment_title}}» بدرجة {{grade}}.',
        ],
        'student.activity.tracked' => [
            'title' => 'نشاط في التعلم',
            'body' => 'تم تسجيل نشاطك على المنصة.',
        ],
        'admin.custom' => [
            'title' => '{{title}}',
            'body' => '{{body}}',
        ],
        'project.team.join_requested' => [
            'title' => 'طلب انضمام لفريق',
            'body' => 'طلب {{user_name}} الانضمام إلى فريق «{{team_name}}» في تحدي «{{challenge_title}}».',
        ],
        'project.team.join_approved' => [
            'title' => 'تم قبول طلب الانضمام',
            'body' => 'تم قبول انضمامك إلى فريق «{{team_name}}» في تحدي «{{challenge_title}}».',
        ],
        'project.team.join_rejected' => [
            'title' => 'تم رفض طلب الانضمام',
            'body' => 'تم رفض طلب انضمامك إلى فريق «{{team_name}}» في تحدي «{{challenge_title}}».',
        ],
        'project.stage.submitted' => [
            'title' => 'تسليم مرحلة مشروع',
            'body' => 'قدّم فريق «{{team_name}}» مرحلة «{{stage_title}}» في تحدي «{{challenge_title}}».',
        ],
        'project.stage.graded' => [
            'title' => 'تم تقييم مرحلة المشروع',
            'body' => 'تم تقييم مرحلة «{{stage_title}}» لفريق «{{team_name}}»: {{score}} من {{max_score}}.',
        ],
        'project.showcase.published' => [
            'title' => 'نُشر عرض المشروع',
            'body' => 'تم نشر عرض «{{showcase_title}}» لفريق «{{team_name}}» في معرض المشاريع.',
        ],
        'project.comment.posted' => [
            'title' => 'تعليق جديد على مشروعك',
            'body' => 'علّق {{commenter_name}} على عرض «{{showcase_title}}».',
        ],
    ],

    'activity_fallbacks' => [
        'student.module.viewed' => [
            'title' => 'عرض محتوى',
            'body' => 'تصفّحتَ «{{module_title}}» ضمن مساق «{{course_title}}».',
        ],
        'student.video.progress' => [
            'title' => 'تقدّم في الفيديو',
            'body' => 'تقدّمك الحالي في «{{module_title}}»: {{progress_percentage}}٪',
        ],
        'student.quiz.previewed' => [
            'title' => 'معاينة اختبار',
            'body' => 'معاينة اختبار «{{quiz_title}}».',
        ],
    ],

    /** عناوين مختصرة عندما لا يتطابق event_key مع أي من الأعلى */
    'event_title_short' => [
        'student.lesson.completed' => 'إكمال درس',
        'student.course.completed' => 'إكمال كورس',
        'student.quiz.completed' => 'اختبار منجز',
        'student.quiz.started' => 'اختبار',
        'student.activity.tracked' => 'نشاط تعليمي',
        'student.assignment.graded' => 'تقييم واجب',
        'student.assignment.submitted' => 'تسليم واجب',
        'student.assignment.available' => 'واجب جديد',
        'project.team.join_requested' => 'طلب انضمام',
        'project.team.join_approved' => 'قبول انضمام',
        'project.team.join_rejected' => 'رفض انضمام',
        'project.stage.submitted' => 'تسليم مرحلة',
        'project.stage.graded' => 'تقييم مرحلة',
        'project.showcase.published' => 'عرض منشور',
        'project.comment.posted' => 'تعليق جديد',
    ],
];
