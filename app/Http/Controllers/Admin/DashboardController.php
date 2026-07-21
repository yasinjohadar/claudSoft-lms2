<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\LoginLog;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $userStats = [
            'students' => User::role('student')->count(),
            'active_today' => LoginLog::whereDate('created_at', $today)->distinct('user_id')->count('user_id'),
        ];

        $courseStats = [
            'total_courses' => Course::count(),
            'published_courses' => Course::where('is_published', true)->count(),
            'total_enrollments' => CourseEnrollment::count(),
            'active_enrollments' => CourseEnrollment::where('enrollment_status', 'active')->count(),
        ];

        $learningStats = [
            'certificates_issued' => Certificate::count(),
        ];

        $todayStats = [
            'certificates_today' => Certificate::whereDate('created_at', $today)->count(),
        ];

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
            'todayStats',
            'quickLinks'
        ));
    }
}
