<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\N8nWebhookEndpoint;
use Illuminate\Support\Str;

class N8nWebhookEndpointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example endpoints - these should be configured based on actual n8n workflows
        $endpoints = [
            [
                'name' => 'Student Enrollment Notification',
                'event_type' => 'student.enrolled',
                'url' => env('N8N_BASE_URL', 'http://localhost:5678') . '/webhook/student-enrolled',
                'secret_key' => Str::random(32),
                'is_active' => false, // Disabled by default until configured
                'retry_attempts' => 3,
                'timeout' => 30,
                'headers' => [
                    'X-Source' => 'LMS',
                ],
                'metadata' => [
                    'purpose' => 'Send notifications when students enroll in courses',
                    'actions' => ['email', 'whatsapp', 'crm_update'],
                ],
                'description' => 'Triggers when a student enrolls in a course. Sends notifications via email and WhatsApp, and updates CRM.',
            ],
            [
                'name' => 'Course Completion Handler',
                'event_type' => 'course.completed',
                'url' => env('N8N_BASE_URL', 'http://localhost:5678') . '/webhook/course-completed',
                'secret_key' => Str::random(32),
                'is_active' => false,
                'retry_attempts' => 3,
                'timeout' => 30,
                'headers' => [
                    'X-Source' => 'LMS',
                ],
                'metadata' => [
                    'purpose' => 'Handle course completion events',
                    'actions' => ['generate_certificate', 'send_congratulations', 'crm_update'],
                ],
                'description' => 'Triggers when a student completes a course. Generates certificate and sends congratulations message.',
            ],
            [
                'name' => 'New User Registration',
                'event_type' => 'user.registered',
                'url' => env('N8N_BASE_URL', 'http://localhost:5678') . '/webhook/user-registered',
                'secret_key' => Str::random(32),
                'is_active' => false,
                'retry_attempts' => 3,
                'timeout' => 30,
                'headers' => [
                    'X-Source' => 'LMS',
                ],
                'metadata' => [
                    'purpose' => 'Welcome new users',
                    'actions' => ['welcome_email', 'crm_add', 'telegram_notification'],
                ],
                'description' => 'Triggers when a new user registers. Sends welcome email and adds to CRM.',
            ],
            [
                'name' => 'Payment Received',
                'event_type' => 'payment.received',
                'url' => env('N8N_BASE_URL', 'http://localhost:5678') . '/webhook/payment-received',
                'secret_key' => Str::random(32),
                'is_active' => false,
                'retry_attempts' => 5,
                'timeout' => 45,
                'headers' => [
                    'X-Source' => 'LMS',
                    'X-Priority' => 'high',
                ],
                'metadata' => [
                    'purpose' => 'Process payment notifications',
                    'actions' => ['invoice_generation', 'accounting_sync', 'receipt_email'],
                ],
                'description' => 'Triggers when payment is received. Generates invoice and syncs with accounting system.',
            ],
            [
                'name' => 'Assignment Graded',
                'event_type' => 'assignment.graded',
                'url' => env('N8N_BASE_URL', 'http://localhost:5678') . '/webhook/assignment-graded',
                'secret_key' => Str::random(32),
                'is_active' => false,
                'retry_attempts' => 3,
                'timeout' => 30,
                'headers' => [
                    'X-Source' => 'LMS',
                ],
                'metadata' => [
                    'purpose' => 'Notify students of graded assignments',
                    'actions' => ['email_notification', 'whatsapp_message', 'parent_notification'],
                ],
                'description' => 'Triggers when an assignment is graded. Notifies student and parents.',
            ],
        ];

        foreach ($endpoints as $endpoint) {
            N8nWebhookEndpoint::updateOrCreate(
                [
                    'event_type' => $endpoint['event_type'],
                    'name' => $endpoint['name'],
                ],
                $endpoint
            );
        }
    }
}
