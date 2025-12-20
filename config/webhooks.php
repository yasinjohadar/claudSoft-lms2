<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WPForms Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WPForms webhooks integration
    |
    */

    'wpforms' => [
        // Secret key for webhook authentication
        // Generate a strong random key: php artisan key:generate --show
        'secret' => env('WPFORMS_WEBHOOK_SECRET'),

        // Optional: IP whitelist for additional security
        // Add your WordPress server IP addresses here
        'allowed_ips' => [
            // '123.456.789.0',
            // '98.76.54.32',
        ],

        // Form ID to submission type mapping
        // Map your WPForms form IDs to submission types
        'form_types' => [
            // Example:
            // '123' => 'enrollment',
            // '456' => 'contact',
            // '789' => 'review',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | n8n Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for n8n integration (bidirectional webhooks)
    |
    */

    'n8n' => [
        // إعدادات الـ webhooks الواردة (n8n → Laravel)
        'incoming' => [
            'secret' => env('N8N_WEBHOOK_SECRET'),
            'allowed_ips' => [
                // أضف IP addresses لخادم n8n هنا
                // '127.0.0.1',
            ],
            'signature_header' => 'X-N8N-Signature',
        ],

        // إعدادات الـ webhooks الصادرة (Laravel → n8n)
        'outgoing' => [
            'default_timeout' => env('N8N_DEFAULT_TIMEOUT', 30), // بالثواني
            'default_retry_attempts' => env('N8N_DEFAULT_RETRY_ATTEMPTS', 3),
            'verify_ssl' => env('N8N_VERIFY_SSL', true),
            'queue_name' => env('N8N_QUEUE_NAME', 'webhooks'), // اسم الـ queue
        ],

        // أنواع الأحداث المتاحة للإرسال إلى n8n
        'available_events' => [
            'student.enrolled' => 'Student Enrolled in Course',
            'user.registered' => 'New User Registered',
            'course.completed' => 'Course Completed',
            'course.review.created' => 'Course Review Created',
            'payment.received' => 'Payment Received',
            'assignment.graded' => 'Assignment Graded',
            'certificate.issued' => 'Certificate Issued',
            'quiz.completed' => 'Quiz Completed',
            'lesson.completed' => 'Lesson Completed',
            'student.unenrolled' => 'Student Unenrolled',
            'course.published' => 'Course Published',
            'instructor.approved' => 'Instructor Approved',
            '*' => 'All Events (Wildcard)',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Other Webhook Sources
    |--------------------------------------------------------------------------
    |
    | Add configuration for other webhook sources here
    |
    */

    // Example for other services:
    // 'stripe' => [
    //     'secret' => env('STRIPE_WEBHOOK_SECRET'),
    // ],

];
