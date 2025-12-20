<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\N8nIncomingWebhookHandler;

class N8nIncomingWebhookHandlerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $handlers = [
            [
                'handler_type' => 'user.create',
                'handler_class' => 'App\\Webhooks\\N8n\\Handlers\\CreateUserHandler',
                'description' => 'Create a new user account in the system',
                'required_fields' => ['name', 'email'],
                'optional_fields' => ['password', 'role', 'phone', 'bio'],
                'is_active' => true,
                'example_payload' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'password' => 'secure_password',
                    'role' => 'student',
                    'phone' => '+1234567890',
                ],
            ],
            [
                'handler_type' => 'user.update',
                'handler_class' => 'App\\Webhooks\\N8n\\Handlers\\UpdateUserHandler',
                'description' => 'Update an existing user account',
                'required_fields' => ['user_id'],
                'optional_fields' => ['name', 'email', 'phone', 'bio', 'role'],
                'is_active' => true,
                'example_payload' => [
                    'user_id' => 123,
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com',
                    'phone' => '+966501234567',
                ],
            ],
            [
                'handler_type' => 'course.enroll',
                'handler_class' => 'App\\Webhooks\\N8n\\Handlers\\EnrollStudentHandler',
                'description' => 'Enroll a student in a course',
                'required_fields' => [], // Flexible: accepts student_id/user_id/student_email and course_id/course_slug/course_code
                'optional_fields' => ['student_id', 'user_id', 'student_email', 'course_id', 'course_slug', 'course_code', 'enrollment_date', 'enrollment_status', 'enrolled_by'],
                'is_active' => true,
                'example_payload' => [
                    'student_email' => 'student@example.com',
                    'course_slug' => 'web-development-basics',
                    'enrollment_status' => 'active',
                ],
            ],
            [
                'handler_type' => 'course.unenroll',
                'handler_class' => 'App\\Webhooks\\N8n\\Handlers\\UnenrollStudentHandler',
                'description' => 'Unenroll a student from a course',
                'required_fields' => [], // Flexible: accepts student_id/user_id/student_email and course_id/course_slug/course_code
                'optional_fields' => ['student_id', 'user_id', 'student_email', 'course_id', 'course_slug', 'course_code', 'reason'],
                'is_active' => true,
                'example_payload' => [
                    'student_email' => 'student@example.com',
                    'course_slug' => 'web-development-basics',
                    'reason' => 'Student request',
                ],
            ],
            [
                'handler_type' => 'notification.send',
                'handler_class' => 'App\\Webhooks\\N8n\\Handlers\\SendNotificationHandler',
                'description' => 'Send a notification to users (email, SMS, in-app)',
                'required_fields' => ['message', 'recipients'],
                'optional_fields' => ['type', 'title', 'channels', 'priority', 'action_url', 'icon'],
                'is_active' => true,
                'example_payload' => [
                    'title' => 'Course Update',
                    'message' => 'New content has been added to your course',
                    'recipients' => [123, 456, 789],
                    'type' => 'info',
                    'channels' => ['email', 'in_app'],
                    'priority' => 'normal',
                    'action_url' => '/courses/123',
                ],
            ],
            [
                'handler_type' => 'whatsapp.send',
                'handler_class' => 'App\\Webhooks\\N8n\\Handlers\\SendWhatsAppHandler',
                'description' => 'Send WhatsApp message via n8n integration',
                'required_fields' => ['phone', 'message'],
                'optional_fields' => ['media_url', 'template_name', 'template_params'],
                'is_active' => true,
                'example_payload' => [
                    'phone' => '+966501234567',
                    'message' => 'Your course certificate is ready!',
                    'media_url' => 'https://example.com/certificate.pdf',
                ],
            ],
            [
                'handler_type' => 'telegram.send',
                'handler_class' => 'App\\Webhooks\\N8n\\Handlers\\SendTelegramHandler',
                'description' => 'Send Telegram message via n8n integration',
                'required_fields' => ['chat_id', 'message'],
                'optional_fields' => ['parse_mode', 'disable_notification', 'reply_markup'],
                'is_active' => true,
                'example_payload' => [
                    'chat_id' => '123456789',
                    'message' => 'You have a new assignment due tomorrow!',
                    'parse_mode' => 'Markdown',
                ],
            ],
            [
                'handler_type' => 'grade.update',
                'handler_class' => 'App\\Webhooks\\N8n\\Handlers\\UpdateGradeHandler',
                'description' => 'Update student grade for assignment or quiz',
                'required_fields' => ['user_id', 'gradable_type', 'gradable_id', 'grade'],
                'optional_fields' => ['feedback', 'graded_by'],
                'is_active' => true,
                'example_payload' => [
                    'user_id' => 123,
                    'gradable_type' => 'assignment',
                    'gradable_id' => 789,
                    'grade' => 95,
                    'feedback' => 'Excellent work!',
                ],
            ],
        ];

        foreach ($handlers as $handler) {
            N8nIncomingWebhookHandler::updateOrCreate(
                ['handler_type' => $handler['handler_type']],
                $handler
            );
        }
    }
}
