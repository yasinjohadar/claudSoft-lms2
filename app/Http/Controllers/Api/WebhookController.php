<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookLog;
use App\Models\WPFormsSubmission;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\FrontendReview;
use App\Models\FrontendCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    /**
     * Handle WPForms webhook
     */
    public function wpforms(Request $request)
    {
        try {
            // Log the webhook
            $webhookLog = WebhookLog::create([
                'source' => 'wpforms',
                'event_type' => $request->input('event_type', 'form_submit'),
                'payload' => $request->all(),
                'headers' => $request->headers->all(),
                'status' => 'received',
                'ip_address' => $request->ip(),
            ]);

            // Extract form data
            $formId = $request->input('form_id') ?? $request->input('form', [])['id'] ?? null;
            $entryId = $request->input('entry_id') ?? $request->input('entry', [])['id'] ?? null;
            $fields = $request->input('fields', []);

            if (!$formId) {
                throw new \Exception('Form ID is missing');
            }

            // Create submission record
            $submission = WPFormsSubmission::create([
                'form_id' => $formId,
                'entry_id' => $entryId,
                'submission_type' => $this->determineSubmissionType($formId, $fields),
                'form_data' => $fields,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Process the submission based on type
            $result = $this->processSubmission($submission);

            $webhookLog->markAsProcessed($result['message']);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'submission_id' => $submission->id,
            ], 200);

        } catch (\Exception $e) {
            Log::error('WPForms Webhook Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);

            if (isset($webhookLog)) {
                $webhookLog->markAsFailed($e->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Determine submission type based on form ID or fields
     */
    private function determineSubmissionType(string $formId, array $fields): string
    {
        // You can map form IDs to submission types
        $formTypeMap = [
            // Add your WPForms form IDs here
            // '123' => 'enrollment',
            // '456' => 'contact',
            // '789' => 'review',
        ];

        // Check if form ID is mapped
        if (isset($formTypeMap[$formId])) {
            return $formTypeMap[$formId];
        }

        // Detect type from field names
        foreach ($fields as $field) {
            $fieldName = strtolower($field['name'] ?? '');
            $fieldValue = strtolower($field['value'] ?? '');

            if (str_contains($fieldName, 'course') || str_contains($fieldName, 'enroll')) {
                return 'enrollment';
            }
            if (str_contains($fieldName, 'review') || str_contains($fieldName, 'rating')) {
                return 'review';
            }
            if (str_contains($fieldName, 'payment') || str_contains($fieldName, 'price')) {
                return 'payment';
            }
        }

        return 'contact'; // Default type
    }

    /**
     * Process the submission based on its type
     */
    private function processSubmission(WPFormsSubmission $submission): array
    {
        $type = $submission->submission_type;

        switch ($type) {
            case 'enrollment':
                return $this->processEnrollment($submission);

            case 'review':
                return $this->processReview($submission);

            case 'contact':
                return $this->processContact($submission);

            case 'payment':
                return $this->processPayment($submission);

            default:
                $submission->markAsProcessed(null, null, 'Unknown submission type');
                return ['message' => 'Submission received but not processed'];
        }
    }

    /**
     * Process course enrollment from WPForms
     */
    private function processEnrollment(WPFormsSubmission $submission): array
    {
        $formData = $submission->form_data;

        // Extract student information
        $name = $this->extractField($formData, ['name', 'full_name', 'student_name']);
        $email = $this->extractField($formData, ['email', 'student_email']);
        $phone = $this->extractField($formData, ['phone', 'mobile', 'telephone']);
        $courseName = $this->extractField($formData, ['course', 'course_name', 'course_title']);

        if (!$email || !$name) {
            throw new \Exception('Name and email are required');
        }

        // Find or create user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make(Str::random(16)), // Random password
                'email_verified_at' => now(),
            ]);

            // Assign student role
            $user->assignRole('student');
        }

        // Find course
        $course = null;
        if ($courseName) {
            $course = Course::where('title', 'like', "%{$courseName}%")
                ->orWhere('slug', 'like', "%{$courseName}%")
                ->first();
        }

        // Create enrollment if course found
        if ($course) {
            $enrollment = CourseEnrollment::firstOrCreate([
                'course_id' => $course->id,
                'student_id' => $user->id,
            ], [
                'enrollment_status' => 'active',
                'enrollment_date' => now(),
            ]);

            $submission->markAsProcessed($user->id, $course->id, 'User enrolled successfully');

            return [
                'message' => "User {$user->email} enrolled in {$course->title}",
            ];
        }

        $submission->markAsProcessed($user->id, null, 'User created but no course found');

        return [
            'message' => "User {$user->email} created but course not found",
        ];
    }

    /**
     * Process course review from WPForms
     */
    private function processReview(WPFormsSubmission $submission): array
    {
        $formData = $submission->form_data;

        // Extract review information
        $name = $this->extractField($formData, ['name', 'full_name', 'student_name', 'reviewer_name']);
        $email = $this->extractField($formData, ['email', 'student_email', 'reviewer_email']);
        $rating = $this->extractField($formData, ['rating', 'stars', 'rate']);
        $reviewText = $this->extractField($formData, ['review', 'review_text', 'comment', 'message', 'feedback']);
        $position = $this->extractField($formData, ['position', 'job_title', 'student_position']);
        $courseName = $this->extractField($formData, ['course', 'course_name', 'course_title']);

        if (!$name || !$email || !$reviewText) {
            throw new \Exception('Name, email, and review text are required');
        }

        // Validate and convert rating
        $ratingValue = 5; // Default rating
        if ($rating) {
            $ratingInt = (int) $rating;
            if ($ratingInt >= 1 && $ratingInt <= 5) {
                $ratingValue = $ratingInt;
            }
        }

        // Find or create user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(16)),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('student');
        }

        // Find course if specified
        $frontendCourse = null;
        if ($courseName) {
            $frontendCourse = FrontendCourse::where('title', 'like', "%{$courseName}%")
                ->orWhere('slug', 'like', "%{$courseName}%")
                ->first();
        }

        // Create review
        $review = FrontendReview::create([
            'user_id' => $user->id,
            'frontend_course_id' => $frontendCourse?->id,
            'student_name' => $name,
            'student_email' => $email,
            'student_position' => $position,
            'rating' => $ratingValue,
            'review_text' => $reviewText,
            'is_active' => false, // Pending admin approval
            'is_featured' => false,
            'order' => 0,
        ]);

        $submission->markAsProcessed($user->id, $frontendCourse?->id, 'Review created successfully (pending approval)');

        return [
            'message' => "Review from {$name} created successfully. Pending admin approval.",
            'review_id' => $review->id,
        ];
    }

    /**
     * Process contact form from WPForms
     */
    private function processContact(WPFormsSubmission $submission): array
    {
        $formData = $submission->form_data;

        // Extract contact information
        $name = $this->extractField($formData, ['name', 'full_name', 'contact_name']);
        $email = $this->extractField($formData, ['email', 'contact_email']);
        $phone = $this->extractField($formData, ['phone', 'mobile', 'telephone', 'contact_phone']);
        $subject = $this->extractField($formData, ['subject', 'topic', 'title']);
        $message = $this->extractField($formData, ['message', 'content', 'body', 'description']);

        if (!$name || !$email || !$message) {
            throw new \Exception('Name, email, and message are required');
        }

        // Try to send email notification to admin
        try {
            $adminEmail = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'admin@example.com'));
            $adminName = config('mail.from.name', env('MAIL_FROM_NAME', 'Admin'));

            // Send email notification
            Mail::send([], [], function ($mail) use ($adminEmail, $adminName, $name, $email, $phone, $subject, $message) {
                $mail->to($adminEmail, $adminName)
                     ->subject('رسالة تواصل جديدة من WPForms: ' . ($subject ?: 'بدون موضوع'))
                     ->html("
                        <h2>رسالة تواصل جديدة</h2>
                        <p><strong>الاسم:</strong> {$name}</p>
                        <p><strong>البريد الإلكتروني:</strong> {$email}</p>
                        " . ($phone ? "<p><strong>الهاتف:</strong> {$phone}</p>" : "") . "
                        " . ($subject ? "<p><strong>الموضوع:</strong> {$subject}</p>" : "") . "
                        <p><strong>الرسالة:</strong></p>
                        <p>" . nl2br(e($message)) . "</p>
                        <hr>
                        <p><small>تم الإرسال من WPForms Webhook</small></p>
                     ");
            });

            $emailStatus = 'Email sent successfully';
        } catch (\Exception $e) {
            Log::warning('Failed to send contact form email', [
                'error' => $e->getMessage(),
                'submission_id' => $submission->id,
            ]);
            $emailStatus = 'Email sending failed: ' . $e->getMessage();
        }

        // Find or create user if email exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make(Str::random(16)),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('student');
        }

        $submission->markAsProcessed($user->id, null, "Contact form received. {$emailStatus}");

        return [
            'message' => "Contact form from {$name} received and processed",
            'email_sent' => str_contains($emailStatus, 'successfully'),
        ];
    }

    /**
     * Process payment from WPForms
     * Note: Payment processing is not implemented as per requirements
     */
    private function processPayment(WPFormsSubmission $submission): array
    {
        // Payment processing is not needed per user requirements
        $submission->markAsProcessed(null, null, 'Payment submission received but processing not implemented');

        return [
            'message' => 'Payment submission received and logged',
        ];
    }

    /**
     * Extract field value from form data by possible field names
     */
    private function extractField(array $formData, array $possibleNames): ?string
    {
        foreach ($formData as $field) {
            $fieldName = strtolower($field['name'] ?? '');
            $fieldId = strtolower($field['id'] ?? '');

            foreach ($possibleNames as $name) {
                if (str_contains($fieldName, $name) || str_contains($fieldId, $name)) {
                    return $field['value'] ?? null;
                }
            }
        }

        return null;
    }

    /**
     * Test endpoint to verify webhook is accessible
     */
    public function test(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Webhook endpoint is working',
            'timestamp' => now()->toIso8601String(),
            'ip' => $request->ip(),
        ]);
    }
}
