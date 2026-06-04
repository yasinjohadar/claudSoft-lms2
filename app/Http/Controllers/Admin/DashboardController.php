<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseReview;
use App\Models\LoginLog;
use App\Models\N8nWebhookEndpoint;
use App\Models\OutgoingWebhookLog;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\WebhookLog;
use App\Models\WPFormsSubmission;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $last30Days = Carbon::today()->subDays(29);

        // Users & roles
        $userStats = [
            'total_users'      => User::count(),
            'students'         => User::role('student')->count(),
            'instructors'      => User::role('instructor')->count(),
            'admins'           => User::role('admin')->count(),
            'active_today'     => LoginLog::whereDate('created_at', $today)->distinct('user_id')->count('user_id'),
            'new_last_30_days' => User::whereDate('created_at', '>=', $last30Days)->count(),
        ];

        // Courses & enrollments
        $courseStats = [
            'total_courses'      => Course::count(),
            'published_courses'  => Course::where('is_published', true)->count(),
            'visible_courses'    => Course::where('is_visible', true)->count(),
            'total_enrollments'  => CourseEnrollment::count(),
            'active_enrollments' => CourseEnrollment::where('enrollment_status', 'active')->count(),
            'completed_enrollments' => CourseEnrollment::where('enrollment_status', 'completed')->count(),
            'pending_enrollments'   => CourseEnrollment::where('enrollment_status', 'pending')->count(),
        ];

        // Learning & assessments
        $learningStats = [
            'total_quizzes'        => Quiz::count(),
            'quiz_attempts'        => QuizAttempt::count(),
            // في جدول quiz_attempts العمود هو "passed" وليس "is_passed"
            'passed_attempts'      => QuizAttempt::where('passed', true)->count(),
            'certificates_issued'  => Certificate::count(),
            'course_reviews'       => CourseReview::count(),
        ];

        // Automation / integrations
        $integrationStats = [
            'n8n_endpoints'      => N8nWebhookEndpoint::count(),
            'outgoing_webhooks'  => OutgoingWebhookLog::count(),
            'failed_webhooks'    => OutgoingWebhookLog::where('status', 'failed')->count(),
            'incoming_webhooks'  => WebhookLog::count(),
            'wpforms_submissions'=> WPFormsSubmission::count(),
        ];

        // Today snapshot
        $todayStats = [
            'new_users'        => User::whereDate('created_at', $today)->count(),
            'new_enrollments'  => CourseEnrollment::whereDate('created_at', $today)->count(),
            'completed_today'  => CourseEnrollment::where('enrollment_status', 'completed')
                ->whereDate('updated_at', $today)
                ->count(),
            'certificates_today' => Certificate::whereDate('created_at', $today)->count(),
        ];

        // Recent activity
        $recentEnrollments = CourseEnrollment::with(['student', 'course'])
            ->latest('enrollment_date')
            ->limit(5)
            ->get();

        $recentCertificates = Certificate::with(['user', 'course'])
            ->latest()
            ->limit(5)
            ->get();

        $recentQuizAttempts = QuizAttempt::with(['student', 'quiz'])
            ->latest()
            ->limit(5)
            ->get();

        $recentWebhooks = OutgoingWebhookLog::latest()
            ->limit(5)
            ->get();

        // Simple chart data: enrollments per month (last 6 months)
        $enrollmentsByMonth = CourseEnrollment::selectRaw('DATE_FORMAT(enrollment_date, "%Y-%m") as month, COUNT(*) as total')
            ->whereDate('enrollment_date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $chartData = [
            'enrollments' => [
                'labels' => $enrollmentsByMonth->pluck('month'),
                'data'   => $enrollmentsByMonth->pluck('total'),
            ],
        ];

        $quizAttempts = $learningStats['quiz_attempts'] ?? 0;
        $quizPassRate = $quizAttempts > 0
            ? round(($learningStats['passed_attempts'] ?? 0) / $quizAttempts * 100)
            : 0;

        $quickLinks = [
            ['route' => 'users.index', 'icon' => 'fe-users', 'title' => 'المستخدمون', 'subtitle' => 'إدارة المستخدمين', 'color' => 'primary'],
            ['route' => 'courses.index', 'icon' => 'fe-book-open', 'title' => 'الكورسات', 'subtitle' => 'إدارة الكورسات', 'color' => 'danger'],
            ['route' => 'enrollments.all', 'icon' => 'fe-user-check', 'title' => 'الالتحاقات', 'subtitle' => 'إدارة الالتحاقات', 'color' => 'success'],
            ['route' => 'admin.certificates.index', 'icon' => 'fe-award', 'title' => 'الشهادات', 'subtitle' => 'إدارة الشهادات', 'color' => 'warning'],
            ['route' => 'quizzes.index', 'icon' => 'fe-file-text', 'title' => 'الاختبارات', 'subtitle' => 'إدارة الاختبارات', 'color' => 'info'],
            ['route' => 'assignments.index', 'icon' => 'fe-edit', 'title' => 'الواجبات', 'subtitle' => 'إدارة الواجبات', 'color' => 'secondary'],
            ['route' => 'question-bank.index', 'icon' => 'fe-help-circle', 'title' => 'بنك الأسئلة', 'subtitle' => 'إدارة الأسئلة', 'color' => 'teal'],
            ['route' => 'invoices.index', 'icon' => 'fe-file', 'title' => 'الفواتير', 'subtitle' => 'إدارة الفواتير', 'color' => 'orange'],
            ['route' => 'payments.index', 'icon' => 'fe-credit-card', 'title' => 'المدفوعات', 'subtitle' => 'إدارة المدفوعات', 'color' => 'pink'],
            ['route' => 'groups.all', 'icon' => 'fe-users', 'title' => 'المجموعات', 'subtitle' => 'إدارة المجموعات', 'color' => 'indigo'],
            ['route' => 'admin.frontend-courses.index', 'icon' => 'fe-globe', 'title' => 'الواجهة', 'subtitle' => 'الكورسات الأمامية', 'color' => 'purple'],
            ['route' => 'admin.settings.email.index', 'icon' => 'fe-mail', 'title' => 'البريد', 'subtitle' => 'إعدادات البريد', 'color' => 'secondary'],
            ['route' => 'admin.n8n.index', 'icon' => 'fe-zap', 'title' => 'n8n', 'subtitle' => 'تكامل n8n', 'color' => 'info'],
            ['route' => 'admin.webhooks.index', 'icon' => 'fe-git-commit', 'title' => 'Webhooks', 'subtitle' => 'الويب هوكس', 'color' => 'teal'],
        ];

        return view('admin.dashboard', compact(
            'userStats',
            'courseStats',
            'learningStats',
            'integrationStats',
            'todayStats',
            'recentEnrollments',
            'recentCertificates',
            'recentQuizAttempts',
            'recentWebhooks',
            'chartData',
            'quizPassRate',
            'quickLinks'
        ));
    }
}


